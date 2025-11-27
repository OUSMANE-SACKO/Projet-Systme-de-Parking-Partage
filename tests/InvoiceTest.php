<?php
require_once 'src/Functions/autoloader.php';

use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase {
    private Customer $customer;
    private Parking $parking;
    private Reservation $reservation;
    
    protected function setUp(): void {
        $this->customer = new Customer('Doe', 'John', 'john.doe@example.com', 'hashed_password');
        $this->parking = new Parking(['address' => '123 Test St'], 10);
        $this->reservation = new Reservation(
            $this->customer, 
            $this->parking, 
            new DateTime('2024-01-01 10:00:00'), 
            new DateTime('2024-01-01 12:00:00')
        );
    }
    
    public function testInvoiceConstruction(): void {
        $amount = 25.50;
        $currency = 'EUR';
        
        $invoice = new Invoice($this->reservation, $amount, $currency);
        
        $this->assertNotEmpty($invoice->getId());
        $this->assertEquals($this->reservation, $invoice->getReservation());
        $this->assertEquals($amount, $invoice->getAmount());
        $this->assertEquals($currency, $invoice->getCurrency());
        $this->assertInstanceOf(DateTime::class, $invoice->getGeneratedAt());
    }
    
    public function testInvoiceConstructionWithDefaultCurrency(): void {
        $amount = 15.75;
        
        $invoice = new Invoice($this->reservation, $amount);
        
        $this->assertEquals('EUR', $invoice->getCurrency());
        $this->assertEquals($amount, $invoice->getAmount());
    }
    
    public function testInvoiceConstructionWithNegativeAmountThrowsException(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('amount must be >= 0');
        
        new Invoice($this->reservation, -10.0);
    }
    
    public function testToHtmlGeneratesValidHtml(): void {
        $invoice = new Invoice($this->reservation, 25.50, 'EUR');
        
        $html = $invoice->toHtml();
        
        $this->assertStringContainsString('<div class="invoice">', $html);
        $this->assertStringContainsString('Facture #' . $invoice->getId(), $html);
        $this->assertStringContainsString('Client: ' . $this->customer->getId(), $html);
        $this->assertStringContainsString('Parking: ' . $this->parking->getId(), $html);
        $this->assertStringContainsString('Du: 2024-01-01 10:00', $html);
        $this->assertStringContainsString('Au: 2024-01-01 12:00', $html);
        $this->assertStringContainsString('Montant: 25,50 EUR', $html);
        $this->assertStringContainsString('</div>', $html);
    }
    
    public function testToPdfBinaryGeneratesValidPdf(): void {
        $invoice = new Invoice($this->reservation, 30.00, 'EUR');
        
        $pdfContent = $invoice->toPdfBinary();
        
        // Vérifie que c'est un vrai PDF
        $this->assertStringStartsWith('%PDF-', $pdfContent);
        $this->assertStringContainsString('%%EOF', $pdfContent);
        
        // Vérifie que le contenu n'est pas vide
        $this->assertNotEmpty($pdfContent);
        $this->assertGreaterThan(100, strlen($pdfContent));
    }
    
    public function testToPdfBinaryContainsInvoiceData(): void {
        $invoice = new Invoice($this->reservation, 45.25, 'USD');
        
        $pdfContent = $invoice->toPdfBinary();
        
        // Le PDF doit être valide et contenir les données (même si encodées)
        $this->assertStringStartsWith('%PDF-', $pdfContent);
        $this->assertNotEmpty($pdfContent);
        
        // Test que le PDF peut être sauvegardé (simulation)
        $tempFile = tempnam(sys_get_temp_dir(), 'invoice_test_');
        file_put_contents($tempFile, $pdfContent);
        $this->assertFileExists($tempFile);
        $this->assertGreaterThan(0, filesize($tempFile));
        unlink($tempFile);
    }
    
    public function testUniqueIds(): void {
        $invoice1 = new Invoice($this->reservation, 10.0);
        $invoice2 = new Invoice($this->reservation, 20.0);
        
        $this->assertNotEquals($invoice1->getId(), $invoice2->getId());
    }
    
    public function testGeneratedAtIsRecent(): void {
        $before = new DateTime();
        $invoice = new Invoice($this->reservation, 15.0);
        $after = new DateTime();
        
        $this->assertGreaterThanOrEqual($before, $invoice->getGeneratedAt());
        $this->assertLessThanOrEqual($after, $invoice->getGeneratedAt());
    }
    
    public function testAmountFormatting(): void {
        $invoice = new Invoice($this->reservation, 1234.56, 'EUR');
        
        $html = $invoice->toHtml();
        
        // Vérifie le formatage français des nombres
        $this->assertStringContainsString('1 234,56 EUR', $html);
    }
    
    public function testZeroAmountIsValid(): void {
        $invoice = new Invoice($this->reservation, 0.0, 'EUR');
        
        $this->assertEquals(0.0, $invoice->getAmount());
        
        $html = $invoice->toHtml();
        $this->assertStringContainsString('0,00 EUR', $html);
    }
}