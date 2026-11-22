<?php

/*
| Maps a DNSC program name (from the `programs` table) to the broad field the scholarship rules use.
| First match wins, so put the specific patterns before the general ones.
| A program that matches nothing becomes "unrecognized": the matching then says "confirm with the
| provider" instead of wrongly rejecting the student. Run `php artisan bakr:check-programs` to see
| how every program in your database is mapped, then add a line here for any that are missing.
*/
return [
    ['/criminolog|\bbscrim\b/i', 'criminology'],
    ['/computer engineering|\bbscpe\b/i', 'engineering_other'],
    ['/information technology|information system|computer science|\bbsit\b|\bbsis\b|\bbscs\b|\bbsemc\b|entertainment and multimedia/i', 'it'],
    ['/electrical engineering|\bbsee\b/i', 'engineering_electrical'],
    ['/civil engineering|\bbsce\b/i', 'engineering_civil'],
    ['/mechanical engineering|\bbsme\b/i', 'engineering_mechanical'],
    ['/industrial engineering|\bbsie\b/i', 'engineering_industrial'],
    ['/engineering/i', 'engineering_other'],
    ['/architecture|\bbsarch\b/i', 'architecture'],
    ['/accountancy|accounting|finance|\bbsa\b|\bbsaf?c\b/i', 'accountancy_finance'],
    ['/hospitality|hotel|restaurant|\bbshm\b|\bbshrm\b/i', 'hospitality'],
    ['/tourism|\bbstm\b/i', 'tourism'],
    ['/disaster resilience|\bbsdrm\b/i', 'disaster_resilience'],   // before the generic "management" rule below
    ['/business|management|entrepreneur|marketing|\bbsba\b|\bbsentrep\b/i', 'business'],
    ['/psycholog|\bbsp\b/i', 'psychology'],
    ['/education|\bbped?\b|\bbeed\b|\bbsed\b|\bbecd\b|teaching|elementary|secondary/i', 'education'],
    ['/agroforestry|agricultur|forestry|agronomy|\bbsaf\b|\bbsa(gri)?\b/i', 'agriculture_forestry'],
    ['/environmental|sustainab|\bbses\b/i', 'environment_sustainability'],
    ['/nursing|midwifery|medical|pharmac|medicine|public health|physical therapy|radiolog|nutrition/i', 'health_medical'],
    ['/social work|social science|political science|public administration|\bbpa\b|humanities|communication|english|filipino|history|sociology|\bbssw\b/i', 'social_science_humanities'],
    ['/marine biology|biology|chemistry|mathematics|physics|statistics|\bbsmb\b|science/i', 'stem_other'],
];
