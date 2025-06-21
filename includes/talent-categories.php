<?php
$talent_categories = [
    'music' => 'Music',
    'tech' => 'Tech',
    'art' => 'Art',
    'writing' => 'Writing',
    'photography' => 'Photography',
    'design' => 'Design',
    'other' => 'Other'
];

function showTalentCategoryOptions($selected = '')
{
    global $talent_categories;
    foreach ($talent_categories as $key => $label) {
        $isSelected = ($selected == $key) ? 'selected' : '';
        echo "<option value=\"$key\" $isSelected>$label</option>";
    }
}
?>