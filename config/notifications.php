<?php

return [
    'cotisation_versee' => ['push' => true, 'sms' => true, 'email' => true],
    'cotisation_recue' => ['push' => true, 'sms' => false, 'email' => false],
    'adhesion_validee' => ['push' => true, 'sms' => true, 'email' => true],
    'adhesion_rejetee' => ['push' => true, 'sms' => true, 'email' => false],
    'nouveau_membre' => ['push' => true, 'sms' => false, 'email' => false],
    'rappel_cotisation' => ['push' => true, 'sms' => true, 'email' => false],
    'distribution_mutuelle' => ['push' => true, 'sms' => true, 'email' => true],
    'remboursement_mutuelle' => ['push' => true, 'sms' => true, 'email' => false],
    'mutuelle_funded' => ['push' => true, 'sms' => true, 'email' => false],
    'mutuelle_closed' => ['push' => true, 'sms' => true, 'email' => true],
];
