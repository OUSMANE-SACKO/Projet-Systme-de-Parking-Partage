<?php

use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase {
    private Customer $customer;
    private Parking $parking;
    private Reservation $reservation;
    
    protected function setUp(): void {
        $this->customer = new Customer('Doe', 'John', 'john.doe@test.com', 'hash');
        $this->parking = new Parking(['address' => '123 Test St'], 10);
        $this->reservation = new Reservation($this->customer, $this->parking, new DateTime('2024-01-01 10:00'), new DateTime('2024-01-01 12:00'));
    }
    
    public function testConstruction(): void {
        $invoice = new Invoice($this->reservation, 25.50, 'EUR');
        
        $this->assertNotEmpty($invoice->getId());
        $this->assertEquals($this->reservation, $invoice->getReservation());
        $this->assertEquals(25.50, $invoice->getAmount());
        $this->assertEquals('EUR', $invoice->getCurrency());
        $this->assertInstanceOf(DateTime::class, $invoice->getGeneratedAt());
        
        $invoice2 = new Invoice($this->reservation, 15.75); // Default EUR
        $this->assertEquals('EUR', $invoice2->getCurrency());
        
        $this->expectException(InvalidArgumentException::class);
        new Invoice($this->reservation, -10.0);
    }
    
    public function testHtmlGeneration(): void {
        $invoice = new Invoice($this->reservation, 1234.56, 'EUR');
        $html = $invoice->toHtml();
        
        $this->assertStringContainsString('<div class="invoice">', $html);
        $this->assertStringContainsString('Facture #' . $invoice->getId(), $html);
        $this->assertStringContainsString('Client: ' . $this->customer->getId(), $html);
        $this->assertStringContainsString('Parking: ' . $this->parking->getId(), $html);
        $this->assertStringContainsString('Du: 2024-01-01 10:00', $html);
        $this->assertStringContainsString('Au: 2024-01-01 12:00', $html);
        $this->assertStringContainsString('1 234,56 EUR', $html); // French formatting
        $this->assertStringContainsString('</div>', $html);
    }
    
    public function testPdfGeneration(): void {
        $invoice = new Invoice($this->reservation, 30.00, 'USD');
        $pdf = $invoice->toPdfBinary();
        
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('%%EOF', $pdf);
        $this->assertGreaterThan(100, strlen($pdf));
        
        // Test file creation
        $tempFile = tempnam(sys_get_temp_dir(), 'invoice_test_');
        file_put_contents($tempFile, $pdf);
        $this->assertFileExists($tempFile);
        $this->assertGreaterThan(0, filesize($tempFile));
        unlink($tempFile);
    }
    
    public function testUniqueIdsAndTiming(): void {
        $before = new DateTime();
        $invoice1 = new Invoice($this->reservation, 10.0);
        $invoice2 = new Invoice($this->reservation, 20.0);
        $after = new DateTime();
        
        $this->assertNotEquals($invoice1->getId(), $invoice2->getId());
        $this->assertGreaterThanOrEqual($before, $invoice1->getGeneratedAt());
        $this->assertLessThanOrEqual($after, $invoice1->getGeneratedAt());
        
        $zeroInvoice = new Invoice($this->reservation, 0.0);
        $this->assertEquals(0.0, $zeroInvoice->getAmount());
        $this->assertStringContainsString('0,00 EUR', $zeroInvoice->toHtml());
    }
}
