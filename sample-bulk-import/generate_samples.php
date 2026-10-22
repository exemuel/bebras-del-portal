<?php

$dir = __DIR__ . '/sample-bulk-import';

$csvLines = [
    'bebras_username,participant_name,school_name,category,award_type,year,file_name',
    'sd_toni_01,Toni Silalahi,SD Swasta HKBP Balige,SiKecil,Winner,2024,sd_toni_01.pdf',
    'sd_maya_02,Maya Simanjuntak,SD Budi Mulia Pangururan,Siaga,Participant,2024,sd_maya_02.pdf',
    'smp_jonathan_03,Jonathan Nababan,SMP Santo Paulus Siborongborong,Penggalang,Winner,2024,smp_jonathan_03.pdf',
    'sma_clara_04,Clara Hutapea,SMA Negeri 1 Balige,Penegak,Participant,2024,sma_clara_04.pdf',
];

file_put_contents($dir . '/peserta_bebras_2024.csv', implode("\n", $csvLines));

$dummyPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF";

$zip = new ZipArchive();
$zip->open($dir . '/sertifikat_bebras_2024.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFromString('sd_toni_01.pdf', $dummyPdf);
$zip->addFromString('sd_maya_02.pdf', $dummyPdf);
$zip->addFromString('smp_jonathan_03.pdf', $dummyPdf);
$zip->addFromString('sma_clara_04.pdf', $dummyPdf);
$zip->close();

echo "Sample bulk import files generated in sample-bulk-import/\n";
