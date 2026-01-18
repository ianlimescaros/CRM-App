<?php
// Controller that renders NOC leasing PDF/preview data.

require_once __DIR__ . '/BaseController.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use setasign\Fpdi\Fpdi;

class NocLeasingController extends BaseController
{
    public function downloadPdf(): void
    {
        // No bearer token required; this is used from the web form.

        // Core NOC header
        $todayDate  = $_POST['today_date'] ?? '';
        $contractNo = $_POST['no_name']    ?? '';

        // Listing / landlord section
        $listingConsultant  = $_POST['listing_consultant']       ?? '';
        $propertyRefNumber  = $_POST['property_reference_nuber'] ?? '';
        $landlordsName      = $_POST['landlords_name']           ?? '';
        $passportNumber     = $_POST['passport_number']          ?? '';
        $passportExpiryDate = $_POST['expiry_date']              ?? '';

        // Property classification (each is its own radio group in the form)
        $propertyTypeChoice = $_POST['property_type_choice'] ?? ''; // villa/apartment/office/retail/warehouse/land
        $furnishingStatus   = $_POST['furnishing_status']    ?? ''; // furnished/unfurnished
        $occupancyStatus    = $_POST['occupancy_status']     ?? ''; // vacant/tenanted

        // Property detail fields
        $vacatingDate = $_POST['vacating_date'] ?? '';
        $buildingName = $_POST['building_name'] ?? '';
        $unit         = $_POST['unit']          ?? '';
        $streetName   = $_POST['street_name']   ?? '';
        $community    = $_POST['community']     ?? '';
        $buaSqft      = $_POST['BUA_sqft']      ?? '';
        $plotSqft     = $_POST['plot_sqft']     ?? '';
        $bedrooms     = $_POST['bedrooms']      ?? '';
        $bathrooms    = $_POST['bathrooms']     ?? '';
        $parking      = $_POST['parking']       ?? '';
        $amount       = $_POST['ammount']       ?? '';

        // Listing terms
        $listingType     = $_POST['listing_type']     ?? ''; // exclusive/non_exclusive
        $listingDuration = $_POST['listing_duration'] ?? ''; // 1_month/2_month/3_month
        $untilDate       = $_POST['Until']           ?? '';

        // Landlord sign-off
        $landlordSignatureName = $_POST['landlord_name'] ?? '';
        $signatureDate         = $_POST['date']          ?? '';

        // Existing NOC fields reused from the simple layout (preview + basic PDF)
        $ownerName        = $_POST['owner_name']        ?? '';
        $tenantName       = $_POST['tenant_name']       ?? '';
        $tenantEmail      = $_POST['tenant_email']      ?? '';
        $tenantPhone      = $_POST['tenant_phone']      ?? '';
        $propertyLocation = $_POST['property_location'] ?? '';
        $propertySize     = $_POST['property_size']     ?? '';
        $propertyTypeText = $_POST['property_type']     ?? '';
        $propertyNumber   = $_POST['property_number']   ?? '';
        $contractFrom     = $_POST['contract_from']     ?? '';
        $contractTo       = $_POST['contract_to']       ?? '';
        $annualRent       = $_POST['annual_rent']       ?? '';
        $annualRentFull   = $_POST['annual_rent_words'] ?? '';
        $securityDeposit  = $_POST['security_deposit']  ?? '';

        // NOC leasing PDF template: flattened official form at storage/templates/noc-template.pdf
        $templatePath = __DIR__ . '/../../storage/templates/noc-template.pdf';
        if (!file_exists($templatePath)) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'NOC leasing template not found.']);
            return;
        }

        // A4 page imported via FPDI; coordinates below are in millimeters.
        // The NOC preview overlay should mirror these SetXY positions.
        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId);

        $pdf->SetFont('Helvetica', '', 10);

        // Helper to display dates as dd-mm-yyyy when entered as yyyy-mm-dd
        $formatDate = static function (string $value): string {
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
            return $value;
        };

        $todayDateOut      = $formatDate($todayDate);
        $passportExpiryOut = $formatDate($passportExpiryDate);
        $vacatingDateOut   = $formatDate($vacatingDate);
        $untilDateOut      = $formatDate($untilDate);
        $signatureDateOut  = $formatDate($signatureDate);

        $formatSpacedDate = static function (string $value): string {
            $digits = preg_replace('/\D/', '', $value);
            if (!$digits) {
                return '';
            }
            $digits = substr($digits, 0, 8);
            $gap = str_repeat(' ', 6);
            $len = strlen($digits);
            if ($len <= 2) {
                return $digits;
            }
            if ($len <= 4) {
                return substr($digits, 0, 2) . $gap . substr($digits, 2);
            }
            return substr($digits, 0, 2) . $gap . substr($digits, 2, 2) . $gap . substr($digits, 4);
        };
        $passportExpiryOutSpaced = $formatSpacedDate($passportExpiryOut) ?: $passportExpiryOut;
        $untilDateOutSpaced = $formatSpacedDate($untilDateOut) ?: $untilDateOut;
        $signatureDateOutSpaced = $formatSpacedDate($signatureDateOut) ?: $signatureDateOut;

        // --- Checkbox / radio-style bullets ---
        // All coordinates below are placeholders; adjust them so each bullet
        // sits inside the correct circle on your NOC template.

        // Property type (villa / apartment / office / retail / warehouse / land)
        if ($propertyTypeChoice === 'villa') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(26.3, 102);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($propertyTypeChoice === 'apartment') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(75.2, 101.2);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($propertyTypeChoice === 'office') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(26.5, 111.3);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($propertyTypeChoice === 'retail') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(76.3, 110.7);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($propertyTypeChoice === 'warehouse') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(130.6, 111.5);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($propertyTypeChoice === 'land') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(166.2, 111.6);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        }

        // Furnishing (furnished / unfurnished)
        if ($furnishingStatus === 'furnished') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(130.2, 101.4);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($furnishingStatus === 'unfurnished') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(165, 102.3);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        }

        // Occupancy (vacant / tenanted)
        if ($occupancyStatus === 'vacant') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(26.4, 119.4);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($occupancyStatus === 'tenanted') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(75, 119.5);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        }

        // Listing type (exclusive / non-exclusive)
        if ($listingType === 'exclusive') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(14.4, 203.1);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($listingType === 'non_exclusive') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(42.7, 203.3);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        }

        // Listing duration (1 / 2 / 3 months)
        if ($listingDuration === '1_month') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(14.4, 209.8);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($listingDuration === '2_month') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(42.7, 209.8);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        } elseif ($listingDuration === '3_month') {
            $pdf->SetFont('Helvetica', '', 40);
            $pdf->SetXY(71.1, 209.9);
            $pdf->Write(5, chr(149));
            $pdf->SetFont('Helvetica', '', 10);
        }

        // --- Text layout for the official NOC form ---

        // Header (date + reference no)


        // Landlord / listing details
        $pdf->SetXY(37, 38);
        $pdf->Write(5, $listingConsultant);

        $pdf->SetXY(136, 38);
        $pdf->Write(5, $propertyRefNumber);

        $pdf->SetXY(36, 54.5);
        $pdf->Write(5, $landlordsName);

        $pdf->SetXY(36, 63);
        $pdf->Write(5, $passportNumber);

        $pdf->SetXY(42, 71);
        $pdf->Write(5, $passportExpiryOutSpaced);

        // Property details
        $pdf->SetXY(118, 119);
        $pdf->Write(5, $vacatingDateOut);

        $pdf->SetXY(32, 127);
        $pdf->Write(5, $buildingName);

        $pdf->SetXY(107, 127.6);
        $pdf->Write(5, $unit);

        $pdf->SetXY(28.5, 136);
        $pdf->Write(5, $streetName);

        $pdf->SetXY(114.5, 136);
        $pdf->Write(5, $community);

        $pdf->SetXY(26, 144);
        $pdf->Write(5, $buaSqft);

        $pdf->SetXY(112.5, 144);
        $pdf->Write(5, $plotSqft);

        $pdf->SetXY(26, 152.5);
        $pdf->Write(5, $bedrooms);

        $pdf->SetXY(70, 152.5);
        $pdf->Write(5, $bathrooms);

        $pdf->SetXY(109, 152.5);
        $pdf->Write(5, $parking);

        // Amount / rent in words (reuses tenancy-style full string)

        $pdf->SetXY(24, 161.3);
        $pdf->Write(5, $annualRentFull);

        // Listing "until" date
        $pdf->SetXY(114, 209.5);
        $pdf->Write(5, $untilDateOutSpaced);

        // Landlord sign-off
        $pdf->SetXY(20, 259);
        $pdf->Write(5, $landlordSignatureName);

        $pdf->SetXY(166, 259);
        $pdf->Write(5, $signatureDateOutSpaced);

        $filename = 'noc-leasing-' . date('Ymd-His') . '.pdf';
        $pdf->Output('I', $filename);
        exit;
    }
}
