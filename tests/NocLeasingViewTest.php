<?php

use PHPUnit\Framework\TestCase;

class NocLeasingViewTest extends TestCase
{
    public function testUntilFieldIsDatePickerWithSpacedPreview(): void
    {
        $view = file_get_contents(__DIR__ . '/../public/views/noc-leasing.php');
        $this->assertStringContainsString('name="Until"', $view, 'Until input must exist in the NOC view');
        $this->assertStringContainsString('type="date"', $view, 'Until should be a date picker (type="date")');
        $this->assertStringContainsString('data-preview="spaced"', $view, 'Until should opt into spaced preview');

        // vacating_date should behave the same (date picker + spaced preview opt-in)
        $this->assertStringContainsString('name="vacating_date"', $view, 'Vacating date input must exist');
        $this->assertStringContainsString('name="vacating_date"', $view, 'Vacating date input must exist');
        $this->assertStringContainsString('name="vacating_date"', $view, 'Vacating date input must exist');
        $this->assertStringContainsString('type="date"', $view, 'Vacating date should be a date picker (type="date")');
        $this->assertStringContainsString('data-preview="spaced"', $view, 'Vacating date should opt into spaced preview');

        // landlord sign-off date should also be a date picker and opt into spaced preview
        $this->assertStringContainsString('name="date"', $view, 'Landlord sign-off date input must exist');
        $this->assertStringContainsString('type="date"', $view, 'Landlord sign-off date should be a date picker (type="date")');
        $this->assertStringContainsString('data-preview="spaced"', $view, 'Landlord sign-off date should opt into spaced preview');

        // JS behaviour: ensure vacating_date preview formats as dd/mm/yyyy (slashed)
        $js = file_get_contents(__DIR__ . '/../public/assets/js/nocLeasing.js');
        $this->assertStringContainsString("key === 'vacating_date'", $js, 'JS should special-case vacating_date');
        $this->assertStringContainsString("mSlashIn[3] + '/' + mSlashIn[2] + '/' + mSlashIn[1]", $js, 'JS should format vacating_date with slashes in preview');

        // Controller: landlord sign-off date should be written to the PDF using the spaced/boxed representation
        $controller = file_get_contents(__DIR__ . '/../src/controllers/NocLeasingController.php');
        $this->assertStringContainsString('$signatureDateOutSpaced', $controller, 'Controller should build a spaced representation for the signature date');
        $this->assertStringContainsString('$pdf->Write(5, $signatureDateOutSpaced)', $controller, 'Controller should write the spaced signature date to the PDF');
    }
}
