    Plugin Name: Custom Booking
    Version: 0.0.0
    Plugin URI: null
    Description: Custom booking
    Author: Siyuan Zuo
    Author URI: https://github.com/legenddaniel
    Text Domain: costom-booking

# File explanation:

## pruduct.json

An data model of new WC_Product_Booking($post->ID)

## person_types.json

An data model of get_person_type()[$post->ID]

## old.css & old.php

Utilities that are deprecated temporarily or permanently

## byoe.js

Client side BRING YOUR OWN EQUIPMENT functionalities

## Woocommerce Booking

### woocommerce-bookings\includes\data-objects\class-wc-product-booking.php

WC_Product_Booking object

## Snippet

    ?>
    <script>console.log(<?php echo $base_price ?>)</script>
    <?php

