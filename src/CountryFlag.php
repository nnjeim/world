<?php

namespace Nnjeim\World;

class CountryFlag
{
    public static function emoji(string $iso2): ?string
    {
        $iso2 = strtoupper($iso2);
        if (! preg_match('/^[A-Z]{2}$/D', $iso2)) {
            return null;
        }

        return html_entity_decode(
            '&#' . (127397 + ord($iso2[0])) . ';&#' . (127397 + ord($iso2[1])) . ';',
            ENT_NOQUOTES,
            'UTF-8'
        );
    }
}
