<?php
return [
    'modules' => [
        'notes_history' => ['name' => 'Device Notes History', 'enabled' => true],
        'bookmarks' => ['name' => 'Device Bookmarks', 'enabled' => true],
        'port_notes' => ['name' => 'Port Notes', 'enabled' => true],
        'qr_codes' => ['name' => 'Device QR Codes', 'enabled' => true],
        'diagnostics' => ['name' => 'Ping / SNMP Diagnostics', 'enabled' => true],
        'description_audit' => ['name' => 'Description Auditor', 'enabled' => true],
        'port_flaps' => ['name' => 'Port Flap Counter', 'enabled' => true],
        'device_tags' => ['name' => 'Device Tags', 'enabled' => true],
    ],
    'description_audit' => ['min_length' => 4, 'flagged_terms' => ['temp','test','unknown','unused']],
    'port_flaps' => ['warning' => 3, 'high' => 10, 'critical' => 25],
    'diagnostics' => ['timeout_seconds' => 3],
];
