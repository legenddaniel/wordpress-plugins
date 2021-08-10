<?php

defined('ABSPATH') || exit;

include_once 'config.php';

include_once 'class-ibid.php';
include_once 'class-ibid-email.php';

is_admin() and include_once 'class-ibid-admin.php';

class_exists('Ibid_Auction') and new Ibid_Auction();
class_exists('Ibid_Auction_Admin') and new Ibid_Auction_Admin();
