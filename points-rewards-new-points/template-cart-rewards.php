<?php

if (!defined('ABSPATH')) {
    exit;
}



?>
    <section class="cr-wrapper">
        <div id="cr-head" class="cr-border">
            <p>You now have <b><?=$points;?></b> Beauty Insider Points</p>
            <p id="cr-clp-switch"><b class="cr-link">Redeem your rewards</b><span class="cr-link cr-no-underline">&nbsp;</span><span class="cr-link cr-no-underline cr-switch cr-switch-off">></span></p>
        </div>
        <div id="cr-main" class="cr-padding cr-border">
            <div>
                <ul class="cr-tabs">
                    <li class="cr-link cr-active">0 - 500</li>
                    <li class="cr-link">500 - 1000</li>
                    <li class="cr-link">1000+</li>
                </ul>
            </div>
            <div id="cr-sliders">
                <div class="cr-padding cr-active"><?=$sliders[0];?></div>
                <div class="cr-padding"><?=$sliders[1];?></div>
                <div class="cr-padding"><?=$sliders[2];?></div>
            </div>
        </div>
    </section>

<?php