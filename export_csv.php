<?php

// Path to your SQLite database file
$dbFile = 'DKV2-GmbH.sqlite'; // Replace with the actual SQLite file name in your repository

// Output CSV file name
$outputFile = 'Vertraege_2025.csv';

try {
    // Connect to the SQLite database
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch rows for kredite_hinzugekommen
    $queryIn = "
        SELECT DATE(Zeitstempel) AS Datum, Betrag AS TotalIn
        FROM Vertraege
        WHERE Zeitstempel >= '2025-01-01'
    ";

    // Query to fetch rows for kredite_rausgekommen
    $queryOut = "
        SELECT DATE(Zeitstempel) AS Datum, Betrag AS TotalOut
        FROM ExBuchungen
        WHERE Zeitstempel >= '2025-01-01' AND Buchungsart = 2
    ";

    // Fetch rows for kredite_hinzugekommen
    $stmtIn = $pdo->prepare($queryIn);
    $stmtIn->execute();
    $inRows = $stmtIn->fetchAll(PDO::FETCH_ASSOC); // Array of rows with Datum and TotalIn

    // Fetch rows for kredite_rausgekommen
    $stmtOut = $pdo->prepare($queryOut);
    $stmtOut->execute();
    $outRows = $stmtOut->fetchAll(PDO::FETCH_ASSOC); // Array of rows with Datum and TotalOut

    // Open a file in write mode
    $file = fopen($outputFile, 'w');

    // Write the CSV header
    fputcsv($file, ['datum', 'kredite_hinzugekommen', 'kredite_rausgekommen']);

    // Write rows for kredite_hinzugekommen
    foreach ($inRows as $row) {
        fputcsv($file, [
            $row['Datum'],
            number_format($row['TotalIn'] / 100, 2, '.', ''), // Convert cents to euros
            '0.00' // No rausgekommen for this row
        ]);
    }

    // Write rows for kredite_rausgekommen
    foreach ($outRows as $row) {
        fputcsv($file, [
            $row['Datum'],
            '0.00', // No hinzugekommen for this row
            number_format($row['TotalOut'] / 100, 2, '.', '') // Convert cents to euros
        ]);
    }

    // Close the file
    fclose($file);

    echo "CSV file '$outputFile' has been created successfully.";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}