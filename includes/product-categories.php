<?php
$product_categories = [
    'tools_resources' => 'Tools & Resources',
    'printables_stationery' => 'Printables & Stationery',
    'digital_products' => 'Digital Products',
    'workshops_tutorials' => 'Workshops & Tutorials',
    'custom_commissions' => 'Custom Commissions',
    'professional_services' => 'Professional Services',
    'event_services' => 'Event Services'
];

function showProductCategoryOptions($selected = '')
{
    global $product_categories;
    foreach ($product_categories as $key => $label) {
        $isSelected = ($selected == $key) ? 'selected' : '';
        echo "<option value=\"$key\" $isSelected>$label</option>";
    }
}
?>