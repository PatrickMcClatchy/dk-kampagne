<?php

// Path to your SQLite database file
$dbFile = 'DKV2-GmbH_20250602_194709.sqlite'; // Replace with the actual SQLite file name in your repository

// Output CSV file name
$outputFile = 'Vertraege_2025.csv';

try {
    // Connect to the SQLite database
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch data from the Vertraege table
    $query = "
        SELECT DATE(Zeitstempel) AS Datum, Betrag AS kredite_hinzugekommen
        FROM Vertraege 
        WHERE Zeitstempel >= '2025-01-01'
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Open a file in write mode
    $file = fopen($outputFile, 'w');

    // Write the CSV header with updated column names
    fputcsv($file, ['datum', 'kredite_hinzugekommen', 'kredite_rausgekommen']);

    // Write rows to the CSV file
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Convert Betrag from cents to euros
        $row['kredite_hinzugekommen'] = $row['kredite_hinzugekommen'] / 100;

        // Add kredite_rausgekommen with a default value of 0
        $row['kredite_rausgekommen'] = 0;

        // Write the row to the CSV file
        fputcsv($file, [
            $row['Datum'], 
            number_format($row['kredite_hinzugekommen'], 2, '.', ''), 
            $row['kredite_rausgekommen']
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