<?php

namespace App\Service;

class Slugger
{
    /**
     * Returns a slug
     */
    public function slugify($stringToSlug)
    {
        return preg_replace( '/[^a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*/', '-', strtolower(trim(strip_tags($stringToSlug))) );
    }
}