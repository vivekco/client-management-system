<?php
namespace App\Services;

class DuplicateDetectionService
{

    public function generateSignature($company, $email, $phone)
    {
        return md5(
            strtolower(trim($company)) .
            strtolower(trim($email)) .
            trim($phone)
        );
    }

}