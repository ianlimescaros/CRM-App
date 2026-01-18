<?php
// Controller that renders tenancy contract PDF/preview data.

require_once __DIR__ . '/BaseController.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use setasign\Fpdi\Fpdi;

class TenancyContractController extends BaseController
{
    public function downloadPdf(): void
    {
        // No bearer token required; this is used from the web form.

        // ==============================
        // 1) Read POST fields (page 1 + extra terms)
        // ==============================
        $todayDate        = $_POST['today_date']        ?? '';
        $contractNo       = $_POST['no_name']           ?? '';
        $propertyUsage    = $_POST['property_usage']    ?? '';
        $ownerName        = $_POST['owner_name']        ?? '';
        $landlordName     = $_POST['landlord_name']     ?? '';
        $landlordEmail    = $_POST['landlord_email']    ?? '';
        $landlordPhone    = $_POST['landlord_phone']    ?? '';
        $tenantName       = $_POST['tenant_name']       ?? '';
        $tenantEmail      = $_POST['tenant_email']      ?? '';
        $tenantPhone      = $_POST['tenant_phone']      ?? '';
        $buildingName     = $_POST['building_name']     ?? '';
        $propertyLocation = $_POST['property_location'] ?? '';
        $propertySize     = $_POST['property_size']     ?? '';
        $propertyType     = $_POST['property_type']     ?? '';
        $propertyNumber   = $_POST['property_number']   ?? '';
        $dewaNumber       = $_POST['dewa_number']       ?? '';
        $plotNumber       = $_POST['plot_number']       ?? '';
        $contractFrom     = $_POST['contract_from']     ?? '';
        $contractTo       = $_POST['contract_to']       ?? '';
        $annualRent       = $_POST['annual_rent']       ?? '';
        $annualRentFull   = $_POST['annual_rent_words'] ?? '';
        $contractValue    = $_POST['contract_value']    ?? '';
        $securityDeposit  = $_POST['security_deposit']  ?? '';
        $modePayment      = $_POST['mode_payment']      ?? '';

        // Free-text additional terms for page 2 (newline-separated from the UI)
        $additionalTerms  = $_POST['additional_terms']  ?? '';

        // ==============================
        // 2) Template check
        // ==============================
        $templatePath = __DIR__ . '/../../storage/templates/tenancy-contract-template.pdf';
        if (!file_exists($templatePath)) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Tenancy contract template not found.']);
            return;
        }

        // ==============================
        // 3) FPDI init + import page 1
        // ==============================
        $pdf = new Fpdi();
        $pdf->AddPage();

        $pageCount = $pdf->setSourceFile($templatePath);

        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId);

        $pdf->SetFont('Helvetica', '', 10);

        // ==============================
        // 4) Helpers
        // ==============================
        $formatDate = static function (string $value): string {
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
            return $value;
        };

        $formatSpacedDate = static function (string $value): string {
            $digits = preg_replace('/\D/', '', $value);
            if (!$digits) return '';
            $digits = substr($digits, 0, 8);
            $gap = str_repeat(' ', 7);
            $len = strlen($digits);
            if ($len <= 2) return $digits;
            if ($len <= 4) return substr($digits, 0, 2) . $gap . substr($digits, 2);
            return substr($digits, 0, 2) . $gap . substr($digits, 2, 2) . $gap . substr($digits, 4);
        };

        $todayDateOut    = $formatDate($todayDate);
        $contractFromOut = $formatDate($contractFrom);
        $contractToOut   = $formatDate($contractTo);

        // Spaced (PDF-boxed) representations — used when filling fixed-width boxes on the template
        $todayDateOutSpaced    = $formatSpacedDate($todayDateOut) ?: $todayDateOut;
        $contractFromOutSpaced = $formatSpacedDate($contractFromOut) ?: $contractFromOut;
        $contractToOutSpaced   = $formatSpacedDate($contractToOut) ?: $contractToOut;

        // Slashed (human-readable) representations — used for contract period when requested
        $contractFromOutSlashed = $contractFromOut ? str_replace('-', '/', $contractFromOut) : '';
        $contractToOutSlashed   = $contractToOut ? str_replace('-', '/', $contractToOut) : '';

        // ==============================
        // 5) PAGE 1 placements (existing)
        // ==============================

        // Property usage bullets
        if ($propertyUsage === 'residential') {
            $pdf->SetFont('Helvetica', '', 35);
            $pdf->SetXY(148, 32);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($propertyUsage === 'commercial') {
            $pdf->SetFont('Helvetica', '', 35);
            $pdf->SetXY(108.5, 32);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($propertyUsage === 'industrial') {
            $pdf->SetFont('Helvetica', '', 35);
            $pdf->SetXY(68.5, 32);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        }

        // Header (date + contract no)
        $pdf->SetXY(16, 15);
        $pdf->Write(5, $todayDateOutSpaced);

        $pdf->SetXY(16, 21);
        $pdf->Write(5, $contractNo);

        // Left column basics
        $pdf->SetXY(24.5, 38);
        $pdf->Write(5, $ownerName);

        $pdf->SetXY(27.5, 46);
        $pdf->Write(5, $landlordName);

        $pdf->SetXY(24, 53);
        $pdf->Write(5, $tenantName);

        $pdf->SetXY(24, 60);
        $pdf->Write(5, $tenantEmail);

        $pdf->SetXY(25, 68);
        $pdf->Write(5, $tenantPhone);

        $pdf->SetXY(128, 68);
        $pdf->Write(5, $landlordPhone);

        $pdf->SetXY(128, 60);
        $pdf->Write(5, $landlordEmail);

        $pdf->SetXY(25, 75);
        $pdf->Write(5, $buildingName);

        $pdf->SetXY(119, 75.5);
        $pdf->Write(5, $propertyLocation);

        $pdf->SetXY(29, 83);
        $pdf->Write(5, $propertySize);

        $pdf->SetXY(103, 83);
        $pdf->Write(5, $propertyType);

        $pdf->SetXY(168, 83);
        $pdf->Write(5, $propertyNumber);

        $pdf->SetXY(33, 90.5);
        $pdf->Write(5, $dewaNumber);

        $pdf->SetXY(117, 90.5);
        $pdf->Write(5, $plotNumber);

        // Contract period and rent
        $pdf->SetXY(35, 97.3);
        // Use slashed, human-readable contract-period in the PDF as requested (preview + PDF match)
        $pdf->Write(5, $contractToOutSlashed . '                                                                ' . $contractFromOutSlashed);

        $pdf->SetXY(23, 104.5);
        if ($annualRentFull !== '') {
            $pdf->Write(5, $annualRentFull);
        } elseif ($annualRent !== '') {
            $pdf->Write(5, 'AED ' . $annualRent);
        }

        $pdf->SetXY(26, 112);
        $pdf->Write(5, $contractValue === '' ? '' : 'AED ' . $contractValue . ' /--');

        $pdf->SetXY(39, 119.5);
        $pdf->Write(5, $securityDeposit === '' ? '' : 'AED ' . $securityDeposit . ' /--');

        $pdf->SetXY(132, 119.5);
        $pdf->Write(5, $modePayment);

        // ==============================
        // 6) PAGE 2 – "additional_terms" mapped line-by-line
        // ==============================
        if ($pageCount >= 2) {
            $pdf->AddPage();
            $tplId2 = $pdf->importPage(2);
            $pdf->useTemplate($tplId2);

            $pdf->SetFont('Helvetica', '', 10);

            // Coordinates for the first dashed line and vertical spacing (mm)
            $termsX       = 12;   // left-right
            $termsYStart  = 150;  // line 1 Y
            $termsLineGap = 8;    // distance between lines

            $additionalTerms = trim((string) $additionalTerms);

            if ($additionalTerms !== '') {
                // Split by real line breaks from the UI
                $rawLines = preg_split('/\r\n|\r|\n/', $additionalTerms);
                $lines = array_values(array_filter(array_map('trim', $rawLines), static function ($line) {
                    return $line !== '';
                }));

                // Up to 8 dashed lines
                $maxLines = 8;
                $lineCount = min(count($lines), $maxLines);

                for ($i = 0; $i < $lineCount; $i++) {
                    $y = $termsYStart + ($i * $termsLineGap);
                    $pdf->SetXY($termsX, $y);
                    $pdf->Write(5, $lines[$i]);
                }
            }
        }

        // ==============================
        // 7) Output
        // ==============================
        $filename = 'tenancy-contract-' . date('Ymd-His') . '.pdf';
        $pdf->Output('I', $filename);
        exit;
    }
}
