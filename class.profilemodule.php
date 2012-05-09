<?php

class ProfileModule extends Gdn_Module {

    public function AssetTarget() {
        return 'Panel';
    }

    public function ToString() {
        $Session = Gdn::Session();
        if ($Session->IsValid() && (C('Plugin.Traditional.SidePanel', TRUE))) {
            ob_start();
            ?>
<div id="ProfileBox" class="Box">
    <div class="AvatarHolder">
        <?php $Builder = UserBuilder($Session->User) ?>
        <?php if(isset($Builder->Photo)){$Builder->Photo = Gdn_Upload::Url(ChangeBasename($Builder->Photo, 'p%s'));} echo UserPhoto($Builder, array('LinkClass'=>'MainMenuImage','ImageClass'=>''))
        ?>  </div>
    <h3 id="UserName"><?php echo $Session->User->Name ?></h3>
    <div id="ProfileStats">
        <dl>
            <dt class="Label">Visits: </dt><dd class="Value"><?php echo Gdn_Format::BigNumber($Session->User->CountVisits) ?></dd>
        </dl>
        <dl>
            <dt class="Label">Comments: </dt><dd class="Value"><?php echo Gdn_Format::BigNumber($Session->User->CountComments) ?></dd>
        </dl>
        <dl>
            <dt class="Label">Messages: </dt><dd class="Value"><?php echo Gdn_Format::BigNumber($Session->User->CountNotifications) ?></dd>
        </dl>
        <dl>
            <dt class="Label">Bookmarks: </dt><dd class="Value"><?php echo Gdn_Format::BigNumber($Session->User->CountBookmarks) ?></dd>
        </dl>
    </div>

    <div style="clear: both"></div>

</div>

            <?php
            $String = ob_get_contents();
		@ob_end_clean();
		return $String;
        }
    }

}