<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render pre-formatted point product slider since wc_get_template() not working properly.
 * @param array $args - Must be [points => '', sliders => [slider1, slider2, slider3]]
 * @return void
 */
function new_point_template_cart_rewards($args)
{
    extract($args);
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
}
