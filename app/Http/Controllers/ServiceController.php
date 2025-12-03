<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Highlight;
use Carbon\Carbon;

class ServiceController extends Controller
{
    public function generateTourTitle($highlights, $startDate)
    {
        // Fetch highlight models from IDs
        $highlightModels = Highlight::whereIn('id', $highlights)->get();
        
        // Parse the start date
        $date = Carbon::parse($startDate);
        $month = $date->format('n'); // 1-12 (no leading zero)
        $day = $date->format('j'); // 1-31 (no leading zero)
        
        // Build the date prefix (month + day, e.g., "118" for January 18)
        $datePrefix = $month . $day;
        
        // Determine the highlight letters
        $highlightLetters = '';
        
        if ($highlightModels->count() > 1) {
            // Multiple highlights: take first letter of each
            foreach ($highlightModels as $highlight) {
                $title = trim($highlight->title);
                if (!empty($title)) {
                    $highlightLetters .= strtoupper(substr($title, 0, 1));
                }
            }
        } else {
            // Single highlight: take first two letters
            $highlight = $highlightModels->first();
            if ($highlight) {
                $title = trim($highlight->title);
                if (!empty($title)) {
                    $highlightLetters = strtoupper(substr($title, 0, 2));
                }
            }
        }
        
        // Format: {month}{date} {letters}H
        // Example: "118 TKH" (January 18, Tanzania + Kenya)
        return $datePrefix . ' ' . $highlightLetters . 'H';
    }
}
