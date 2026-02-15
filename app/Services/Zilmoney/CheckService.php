<?php

namespace App\Services\Zilmoney;

use App\Models\Zilmoney\Payment; // Updated model import
use Barryvdh\DomPDF\Facade\Pdf;
use NumberFormatter;

class CheckService
{
    /**
     * Generate a PDF check for the given payment.
     *
     * @param Payment $payment
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateCheckPdf(Payment $payment)
    {
        // 1. Convert amount to words
        $amountInWords = $this->convertAmountToWords($payment->amount);

        // 2. Load View
        $pdf = Pdf::loadView('zilmoney.pdf.check-template', [
            'payment' => $payment,
            'amountInWords' => $amountInWords,
        ]);

        // 3. Set Paper Size (Letter)
        $pdf->setPaper('letter', 'portrait');

        return $pdf;
    }

    /**
     * Convert numeric amount to words (e.g., "One Hundred Dollars and 00/100").
     */
    /**
     * Convert numeric amount to words (e.g., "One Hundred Dollars and 00/100").
     */
    private function convertAmountToWords($amount)
    {
        $dollars = floor($amount);
        $cents = round(($amount - $dollars) * 100);

        // Simple number to words implementation handling up to millions
        $words = $this->numberToWords($dollars);
        
        return ucwords("$words and $cents/100");
    }

    private function numberToWords($number) 
    {
        $hyphen      = '-';
        $conjunction = ' ';
        $separator   = ' ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = [
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'forty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        ];

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->numberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = (int) ($number / 100);
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                     $string .= $conjunction . $this->numberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->numberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}
