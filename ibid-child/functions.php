<?php

defined('ABSPATH') || exit;

include_once 'config.php';

include_once 'includes/class-ibid.php';
include_once 'includes/class-ibid-email.php';

is_admin() and include_once 'includes/class-ibid-admin.php';

class_exists('Ibid_Auction') and new Ibid_Auction();
class_exists('Ibid_Auction_Admin') and new Ibid_Auction_Admin();

include_once 'add-my-account-item.php';
