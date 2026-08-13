<?php

namespace Saccharine\Documents\Support;

class DemoTemplates
{
    public static function getContractorHtml(): string
    {
        return <<<'HTML'
<style>
    body { font-family: sans-serif; font-size: 14px; color: #333; line-height: 1.4; }
    .header { text-align: center; border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 20px; }
    .grid { display: flex; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; }
    .col { width: 48%; }
    .category-box { border: 1px solid #eee; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th, td { padding: 6px; border-bottom: 1px solid #f9f9f9; text-align: left; }
    .right { text-align: right; }
    .subtotal td { border-top: 1px solid #333; font-weight: bold; }
    .totals-box { width: 40%; float: right; border: 2px solid #333; padding: 10px; }
    .terms { margin-top: 40px; font-size: 11px; clear: both; border-top: 1px solid #ccc; padding-top: 10px; }
</style>

<div class="header">
    <h1 style="margin:0;">{{ $business['name'] }}</h1>
    <p style="margin:5px 0 0 0;">{{ $business['address'] }} | {{ $business['phone'] }} | {{ $business['email'] }}</p>
</div>

<div class="grid">
    <div class="col">
        <h3 style="margin-bottom: 5px;">Client Information</h3>
        <strong>{{ $client['name'] }}</strong><br>
        {{ $client['address'] }}<br>
        {{ $client['email'] }}<br>
        {{ $client['phone'] }}
    </div>
    <div class="col" style="text-align: right;">
        <h3 style="margin-bottom: 5px;">Project Details</h3>
        <strong>Date:</strong> {{ $date }}<br>
        <strong>Project #:</strong> {{ $project_id }}<br>
        <strong>Project Manager:</strong> {{ $manager }}
    </div>
</div>

<h3>Itemized Statement of Work & Materials</h3>

<div class="grid">
    @foreach($categories as $category)
        <div class="col category-box">
            <h4 style="margin-top:0; border-bottom: 1px solid #ccc; padding-bottom:5px;">{{ $category['name'] }}</h4>
            <table>
                @foreach($category['items'] as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="right">${{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal">
                    <td>Subtotal</td>
                    <td class="right">${{ number_format($category['subtotal'], 2) }}</td>
                </tr>
            </table>
        </div>
    @endforeach
</div>

<div class="totals-box">
    <table style="margin:0;">
        <tr>
            <td>Total Tax (HST 13%):</td>
            <td class="right">${{ number_format($tax_amount, 2) }}</td>
        </tr>
        <tr class="subtotal" style="font-size: 16px;">
            <td><strong>Grand Total:</strong></td>
            <td class="right"><strong>${{ number_format($grand_total, 2) }}</strong></td>
        </tr>
    </table>
</div>

<div class="terms">
    <h3 style="margin-bottom: 5px;">Terms & Conditions</h3>
    <p>This document constitutes a binding agreement between the Client and {{ $business['name'] }}. 
    
    Payment Terms: 
    @if($payment_method === 'milestone')
        <strong>25% Due upon signing, 50% at midpoint inspection, 25% upon final completion</strong>.
    @else
        <strong>Net 30 Days upon invoice generation</strong>.
    @endif
    </p>

    @if($has_hazardous_materials)
        <h4 style="color: red;">Hazardous Materials Disclosure</h4>
        <p>Notice: This property has been identified as containing hazardous materials (e.g., asbestos or lead paint). Additional abatement protocols and municipal disposal fees apply as outlined in the disbursements section above. The Client acknowledges that unforeseen structural issues discovered during abatement may require change orders.</p>
    @else
        <h4>Standard Structural Warranty</h4>
        <p>All labor is warrantied for a period of 2 years from the date of completion. Manufacturer warranties apply to all installed fixtures.</p>
    @endif
</div>
HTML;
    }

    public static function getContractorJson(): string
    {
        return <<<'JSON'
{
  "business": {
    "name": "Apex Renovations & Build",
    "address": "404 Builder Lane, Toronto, ON M4B 1B3",
    "phone": "(416) 555-0199",
    "email": "projects@apexrenobuild.com"
  },
  "client": {
    "name": "Robert Chen",
    "address": "789 Pine Avenue, Mississauga, ON L5G 2R8",
    "phone": "(905) 555-8822",
    "email": "rchen_home@example.com"
  },
  "date": "2026-08-13",
  "project_id": "PRJ-2026-8812",
  "manager": "Sarah Jenkins",
  "payment_method": "milestone",
  "has_hazardous_materials": true,
  "categories": [
    {
      "name": "Labor & Services",
      "items": [
        { "description": "Demolition & Site Prep", "amount": 1500.00 },
        { "description": "Custom Carpentry (Kitchen Cabinetry)", "amount": 4200.00 }
      ],
      "subtotal": 5700.00
    },
    {
      "name": "Materials & Fixtures",
      "items": [
        { "description": "Quartz Countertops (Premium Tier)", "amount": 3100.00 },
        { "description": "Hardwood Flooring (Oak, 500 sq ft)", "amount": 2500.00 }
      ],
      "subtotal": 5600.00
    },
    {
      "name": "Permits & Subcontractors (Non-Taxable)",
      "items": [
        { "description": "Municipal Building Permit", "amount": 250.00 },
        { "description": "Licensed Electrical Inspection", "amount": 175.00 }
      ],
      "subtotal": 425.00
    }
  ],
  "tax_amount": 1469.00,
  "grand_total": 13194.00
}
JSON;
    }
}