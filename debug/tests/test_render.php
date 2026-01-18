<?php
// Just check what the contacts.php view contains
echo "=== Analyzing contacts.php view file ===\n\n";

$contactsPath = __DIR__ . '/public/views/contacts.php';
$contactsContent = file_get_contents($contactsPath);

// Check for required elements
$checks = [
    'data-page="contacts"' => 'Page wrapper',
    'id="contactsTableBody"' => 'Table body',
    'id="contactAddBtn"' => 'Add button',
    'id="contactFormContainer"' => 'Form container',
    'id="contactForm"' => 'Form element',
    'id="contactSearch"' => 'Search input',
];

foreach ($checks as $needle => $label) {
    $found = strpos($contactsContent, $needle) !== false;
    echo ($found ? "✓" : "✗") . " $label: $needle\n";
}

echo "\nFile size: " . strlen($contactsContent) . " bytes\n";

// Show the first few lines
echo "\n=== FIRST 300 CHARS ===\n";
echo substr($contactsContent, 0, 300) . "\n";
?>
