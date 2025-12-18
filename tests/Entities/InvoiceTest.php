<?php

use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase {
    private Customer $customer;
    private Parking $parking;
    private Reservation $reservation;
    
    protected function setUp(): void {
        $this->customer = new Customer('Doe', 'John', 'john.doe@test.com', 'hash');
        $this->parking = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 10);
        $this->reservation = new Reservation($this->customer, $this->parking, new DateTime('2024-01-01 10:00'), new DateTime('2024-01-01 12:00'));
    }
    
    public function testConstruction(): void {
        $invoice = new Invoice($this->reservation, 25.50, 'EUR');
        
        
        
        $this->assertSame($this->reservation, $invoice->getReservation());
        $this->assertSame(25.50, $invoice->getAmount());
        $this->assertEquals('EUR', $invoice->getCurrency());
        $this->assertInstanceOf(DateTime::class, $invoice->getGeneratedAt());
        
        // Test default currency
        $invoice2 = new Invoice($this->reservation, 15.75);
        $this->assertEquals('EUR', $invoice2->getCurrency());
        
        // Test zero amount (boundary)
        $zeroInvoice = new Invoice($this->reservation, 0.0);
        $this->assertSame(0.0, $zeroInvoice->getAmount());
        
        // Test amount rounding
        $roundedInvoice = new Invoice($this->reservation, 25.999);
        $this->assertSame(26.0, $roundedInvoice->getAmount());
        
        // Test currency conversion to uppercase
        $lowercaseInvoice = new Invoice($this->reservation, 100.0, 'usd');
        $this->assertEquals('USD', $lowercaseInvoice->getCurrency());
        
        // Test negative amount validation
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('amount must be > 0');
        new Invoice($this->reservation, -10.0);
    }
    
    public function testHtmlGeneration(): void {
        $invoice = new Invoice($this->reservation, 1234.56, 'EUR');
        $html = $invoice->toHtml();
        
        // Test structure and content
        $this->assertStringStartsWith('<div class="invoice">', $html);
        $this->assertStringEndsWith('</div>', $html);
        $this->assertStringContainsString('Facture #' . $invoice->getId(), $html);
        $this->assertStringContainsString('Client: ' . $this->customer->getId(), $html);
        $this->assertStringContainsString('Parking: ' . $this->parking->getId(), $html);
        $this->assertStringContainsString('Du: 2024-01-01 10:00', $html);
        $this->assertStringContainsString('Au: 2024-01-01 12:00', $html);
        
        // Test French formatting (critical for mutations)
        $this->assertStringContainsString('1 234,56 EUR', $html);
        $this->assertStringNotContainsString('1234.56 EUR', $html);
        $this->assertStringNotContainsString('1,234.56 EUR', $html);
        
        // Test zero amount formatting
        $zeroInvoice = new Invoice($this->reservation, 0.0);
        $this->assertStringContainsString('0,00 EUR', $zeroInvoice->toHtml());
        
        // Count occurrences to detect duplication mutations
        $this->assertEquals(1, substr_count($html, '<div class="invoice">'));
        $this->assertEquals(1, substr_count($html, '</div>'));
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
        
        // Test IDs are null until persisted
        $this->assertNull($invoice1->getId());
        $this->assertNull($invoice2->getId());
        
        // Objects should have different amounts
        $this->assertEquals(10.0, $invoice1->getAmount());
        $this->assertEquals(20.0, $invoice2->getAmount());
        
        // Test timing bounds
        $this->assertGreaterThanOrEqual($before, $invoice1->getGeneratedAt());
        $this->assertLessThanOrEqual($after, $invoice1->getGeneratedAt());
        
        // Test that both invoices have timestamps
        $this->assertNotNull($invoice1->getGeneratedAt());
        $this->assertNotNull($invoice2->getGeneratedAt());
    }
    
    public function testValidationAndBoundaries(): void {
        // Test boundary conditions
        $zeroInvoice = new Invoice($this->reservation, 0.0);
        $this->assertSame(0.0, $zeroInvoice->getAmount());
        
        $minValidInvoice = new Invoice($this->reservation, 0.001);
        $this->assertSame(0.0, $minValidInvoice->getAmount()); // Rounded to 0.00
        
        // Test negative validation with different values
        $negativeAmounts = [-0.001, -1.0, -10.0];
        foreach ($negativeAmounts as $amount) {
            try {
                new Invoice($this->reservation, $amount);
                $this->fail('Should throw exception for negative amount: ' . $amount);
            } catch (InvalidArgumentException $e) {
                $this->assertEquals('amount must be > 0', $e->getMessage());
            }
        }
        
        // Backend accepts any currency code without validation
        $shortCurrency = new Invoice($this->reservation, 100.0, 'AB');
        $this->assertEquals('AB', $shortCurrency->getCurrency());
        
        $longCurrency = new Invoice($this->reservation, 100.0, 'ABCD');
        $this->assertEquals('ABCD', $longCurrency->getCurrency());
    }
    
    public function testNumberFormatMutations(): void {
        // Test critical number_format parameters (decimals, separators)
        $testCases = [
            [1234.56, '1 234,56'],
            [1000.00, '1 000,00'],
            [0.01, '0,01']
        ];
        
        foreach ($testCases as [$amount, $expectedFormat]) {
            $invoice = new Invoice($this->reservation, $amount);
            $html = $invoice->toHtml();
            
            // Test correct French formatting
            $this->assertStringContainsString($expectedFormat . ' EUR', $html);
        }
    }
    
    public function testIncrementDecrementMutations(): void {
        // Test number_format decimals parameter (2 should not be 1 or 3)
        $invoice = new Invoice($this->reservation, 123.456);
        $html = $invoice->toHtml();
        
        // Should have exactly 2 decimals
        $this->assertStringContainsString('123,46 EUR', $html);
        
        // Test rounding precision (should be exactly 2, not 1 or 3)
        $precisionTest = new Invoice($this->reservation, 99.999);
        $this->assertEquals(100.0, $precisionTest->getAmount()); // Rounded to 2 decimals
        
        // Valid 3-character currency should work
        $validCurrency = new Invoice($this->reservation, 100.0, 'USD');
        $this->assertEquals('USD', $validCurrency->getCurrency());
    }
    
    public function testArrayStructureIntegrity(): void {
        $invoice = new Invoice($this->reservation, 100.0);
        $html = $invoice->toHtml();
        
        // Test that HTML has exactly the right number of elements
        $expectedElements = [
            '<div class="invoice">',
            '<h2>',
            '</h2>',
            '<p>Client:',
            '<p>Parking:',
            '<p>Du:',
            '<p>Au:',
            '<p>Montant:',
            '<p>Généré le:',
            '</div>'
        ];
        
        foreach ($expectedElements as $element) {
            $count = substr_count($html, $element);
            $this->assertEquals(1, $count, "Element '{$element}' should appear exactly once");
        }
        
        // Test HTML structure order
        $openDivPos = strpos($html, '<div class="invoice">');
        $closeDivPos = strpos($html, '</div>');
        $this->assertLessThan($closeDivPos, $openDivPos, 'Opening div should come before closing div');
        
        // Test that removing any part would break the structure
        $this->assertStringStartsWith('<div class="invoice">', $html);
        $this->assertStringEndsWith('</div>', $html);
    }
    
    public function testNumericBoundaries(): void {
        // Test edge cases for increment/decrement mutations
        $testCases = [
            0.0,   // Boundary value
            0.01,  // Minimum meaningful amount
            1.0,   // Integer amount  
            9.99,  // Common price ending
            10.0,  // Round number
            99.99, // Common price
            100.0  // Another round number
        ];
        
        foreach ($testCases as $amount) {
            $invoice = new Invoice($this->reservation, $amount);
            
            // Test exact amount (no increment/decrement)
            $this->assertEquals($amount, $invoice->getAmount());
            
            // Test that it's not off by 0.01 (common mutation)
            if ($amount >= 0.01) {
                $this->assertNotEquals($amount - 0.01, $invoice->getAmount());
            }
            $this->assertNotEquals($amount + 0.01, $invoice->getAmount());
            
            // Test HTML formatting for these amounts
            $html = $invoice->toHtml();
            $expectedFormat = number_format($amount, 2, ',', ' ');
            $this->assertStringContainsString($expectedFormat . ' EUR', $html);
        }
    }
}

