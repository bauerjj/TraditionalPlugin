<?php if (!defined('APPLICATION')) exit(); ?>
<style>

ol, ul {
    list-style: circle;
    margin-left: 50px;
}


</style>


<h1><?php //echo T($this->Data['Title']); ?></h1>
<div class="Info">
   <?php echo T($this->Data['PluginDescription']); ?>
</div>
<h3>Known Issues</h3>
<ul>
    <li>
        Setup permissions for each category to disallow any new threads in categories with children by editing the permissions on the dashboard
    </li>
</ul>
<h3>TODO List (in order of priority)</h3>
<ul>
    <li><?php echo Anchor('Use LESS CSS','http://lesscss.org/',FALSE) ?></li>
    <li>Theme Switcher</li>
    <li>Hover and preview</li>
    <li>Member List</li>
    <li>Cleanup plugin code</li>

</ul>
<h3><?php echo T('Settings'); ?></h3>
<?php
   echo $this->Form->Open();
   echo $this->Form->Errors();
?>
<br/>

<div class="FilterMenu">
    <ul>
    <li><?php
      echo $this->Form->Label('Breadcrumb Home Link', 'Plugin.Traditional.BreadcrumbHome');
      echo $this->Form->Textbox('Plugin.Traditional.BreadcrumbHome');
   ?></li>
    <li><?php
      echo $this->Form->Label('Full URL to your main homepage', 'Plugin.Traditional.QuickNavMainHome');
      echo $this->Form->Textbox('Plugin.Traditional.QuickNavMainHome');
   ?>
    </li>
    <li><?php
      echo $this->Form->CheckBox('Plugin.Traditional.SidePanel','Enable the Sidepanel?');
   ?>
    </li>
    <li><?php
      echo $this->Form->Label('Width of Body (px or %) but must specify!', 'Plugin.Traditional.BodyWidth');
      echo $this->Form->Textbox('Plugin.Traditional.BodyWidth');
   ?>
    </li>
    <li><?php
      echo $this->Form->CheckBox('Plugin.Traditional.SubCategories','Show grouped subcategories?');
   ?>
    </li>
    <li><?php
      echo $this->Form->CheckBox('Plugin.Traditional.SubTableHeader','Show the header under each cateogry group?');
   ?>
    </li>

    </ul>
</div>
<?php
   echo $this->Form->Close('Save');
?>
<h3>Changelog</h3>
<br/>
20120424: Requires  Traditional Chocolate <b style="color: red">20120424 or Later</b>
<ul>
    <li>
        Added IE6 compatability
    </li>
    <li>
        Added Locale definitions
    </li>
    <li>
        Added minimize/maximize categories on the 'categories/all' page
    </li>
    <li>
        Added "recent activity" and "what's new" along with RSS feed
    </li>
    <li>
        Added sub header bar below main header describing what each column is
    </li>
    <li>
        Added expand/minimize category 
    </li>
    <li>
        Fixed breadcrumb home link not saving
    </li>
    <li>
        Fixed Fatal Errors which would occur if category had no description
    </li>
    <li>
        Fixed Table overflow on discussions caused by faulty markup
    </li>
    <li>
        Validated most of the HTML for XHTML 1.0 Strict
    </li>
    <li>
        Fixed search bar position
    </li>
    <li>
        More general IE6/7 compatability tweaks
    </li>

</ul>
<br/>
B20120416: Requires Traditional Chocolate <b style="color: red">B20120416 or Later</b>
<ul>
    <li>
        Fixed countless IE7 compatability issues (now its pretty solid)
    </li>
    <li>
        Fixed missing images
    </li>
    <li>
        Fixed logo being cut-off
    </li>
    <li>
        Fixed user images not showing up using gravatr/identicon
    </li>
    <li>
        Added sidepanel optional as well as grouped subcatagories (BETA)
    </li>
    <li>
        Added 'Body' width variable
    </li>
    <li>
        Added numbered pager to top of discussions as well as bottom
    </li>
    <li>
        Added the Dark Chocolate Theme more stylish....other general tweaks
    </li>

</ul>
A20120321 - Initial Release