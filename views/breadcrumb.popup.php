<div id="QuickNavContainer">
    <h2>Quick Navigation Manager</h2>
    <div id="QuickLeftNav" class="QuickNav">
        <h4>General Links </h4>
        <ul>
            <li>
            <?php
            echo Anchor('Home',C('Plugin.Mcu.QuickNavMainHome'),'','',FALSE);
            ?>
            </li>
            <li>
               <?php
            echo Anchor("What's New?",'/activity');
            ?>
            </li>
            <li>
               <?php
            echo Anchor("All discussions",'/discussions');
            ?>
            </li>
        </ul>
    </div>
    <div id="QuickRightNav" class="QuickNav">
        <h4>Categories </h4>
        <ul>
            <?php
            foreach ($this->Data['CategoryList'] as $Category) {
                echo '<li class="QuickNav' . $Category['Depth'] . '">' . Anchor($Category['Name'], $Category['UrlCode']) . '</li>';
            }
            ?>
        </ul>


    </div>
    <div style="clear: both"> </div>
</div>