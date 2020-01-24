<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

?>

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <img src="../modules/addons/smsmanager/assets/majority_logo.ico" style="margin: 6px;float:left" height="40px;">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mod_apu_bt_navbar_menu" aria-expanded="false" aria-controls="navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a style="line-height:13px;color:#034048;text-shadow:0.3px 0.3px 0.4px #0f7d8c" class="navbar-brand">Jisort<br><span style="font-size:10px;color:#f53f3f;text-shadow:0.3px 0.3px 0.4px #ff0d0d">for WHMCS</span></a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="mod_apu_bt_navbar_menu">
            <ul class="nav navbar-nav">
                <li><a href="addonmodules.php?module=<?php echo $modulename; ?>"><?php echo $LANG['home']; ?></a></li>
                <li><a href="addonmodules.php?module=<?php echo $modulename; ?>&a=mass"><?php echo $LANG['mass']; ?></a></li>
                <li><a href="addonmodules.php?module=<?php echo $modulename; ?>&a=logs"><?php echo $LANG['logs']; ?></a></li>
                <li><a href="addonmodules.php?module=<?php echo $modulename; ?>&a=management"><?php echo $LANG['management']; ?></a></li>
                <li><a href="addonmodules.php?module=<?php echo $modulename; ?>&a=config"><?php echo $LANG['config']; ?></a></li>
                <li><a href="addonmodules.php?module=<?php echo $modulename; ?>&a=admincontact"><?php echo $LANG['admincontact']; ?></a></li>
                <?php
                if (strtolower($vars['smsgateway']) == "africastalking") {

                    ?>
                    <li><a href="addonmodules.php?module=<?php echo $modulename; ?>&a=optout"><?php echo $LANG['suboptout']; ?></a></li>

                    <?php
                }

                ?>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="addonmodules.php?module=<?php echo $modulename; ?>&a=addon">About Addon</a>
                </li>
            </ul>

        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>