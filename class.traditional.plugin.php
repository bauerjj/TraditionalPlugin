<?php
if (!defined('APPLICATION'))
    exit();
/*
  Copyright 2008, 2009 Vanilla Forums Inc.
  This file is part of Garden.
  Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
  Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
  You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
  Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
 */

// Define the plugin:
$PluginInfo['Traditional'] = array(
    'Description' => 'To be used in conjunction with the Traditional theme. This is a more contemporary layout with a dark theme',
    'Version' => '20120424',
    'RequiredApplications' => array('Vanilla' => '2.0'),
    'RequiredTheme' => TRUE,
    'RequiredPlugins' => FALSE,
    'HasLocale' => TRUE,
    'SettingsUrl' => '/plugin/traditional',
    'SettingsPermission' => 'Garden.AdminUser.Only',
    'Author' => "mcuhq",
    'AuthorEmail' => 'info@mcuhq.com',
    'AuthorUrl' => 'http://mcuhq.com'
);

class TraditionalPlugin extends Gdn_Plugin {

    public $SubCategory = '';
    public $Offset = 0;
    public $count = 0; // keeps track total posts in thread

    public function __construct() {

        //$Expiration = time() + 172800;
//        if(!empty($_COOKIE['VanillaCat']))
//            print_r(($_COOKIE['VanillaCat']));
    }



//@todo...LESS CSS implementation in next release

//        if (property_exists($Sender, 'Head') && is_object($Sender->Head)) {
//            $Sender->Head->AddString(
//                    '<link rel="stylesheet/less" type="text/css" href="/' . Gdn::Request()->WebRoot() . '/plugins/Traditional/design/styles.less" />'
//                    . '<script src="/' . Gdn::Request()->WebRoot() . '/plugins/Traditional/js/less-1.3.0.min.js" type="text/javascript"></script>'
//            );
//        }
//        $Sender->AddJsFile('less-1.3.0.min.js', 'plugins/Traditional/'); //for LESS CSS -> Regular CSS
        //@warning in a production envirnoment, use the compiled CSS without always needing to convert on every page load
        //add this after the .less stylesheet

    /**
     * Base_Render_Before Event Hook
     *
     * @param $Sender Sending controller instance
     */
    public function Base_Render_Before($Sender) {
        $Sender->AddCssFile('/plugins/Traditional/design/breadcrumb.popup.css');
        $Sender->AddJsFile('jquery.defaults.js', 'plugins/Traditional/'); //default "search" text in textbox
        if ($Sender->ControllerName == 'categoriescontroller')
            $Sender->AddJsFile('jquery.cookie.js', 'plugins/Traditional/'); //keep track of which categories are expanded/hidden

        //News ticker stuff
        if ($Sender->SelfUrl == 'activity' || ((strpos($Sender->SelfUrl, 'discussions/p') !== FALSE)) || $Sender->SelfUrl === 'discussions') {
            $Sender->AddJsFile('jquery.ticker.js', 'plugins/Traditional/'); //for LESS CSS -> Regular CSS
            $Sender->AddCssFile('/plugins/Traditional/design/ticker-style.css');
            if (property_exists($Sender, 'Head') && is_object($Sender->Head)) {
                $Sender->Head->AddString(
                        '<script type="text/javascript">
                        $(function () {
                            $("#js-news").ticker({
                                htmlFeed: false,
                                ajaxFeed: true,
                                feedUrl: "' . Gdn::Request()->Domain() . '/' . Gdn::Request()->WebRoot() . '/discussions.rss",
                                feedType: "xml"
                            });
                        });
                    </script>');
            }
        }


        //TEMPPORARYRYRYRYRYRYRY
        if ($Sender->SelfUrl != 'categories/all')
            return;
        include_once(PATH_PLUGINS . DS . 'Traditional' . DS . 'class.profilemodule.php');
        $ProfileModule = new ProfileModule($Sender);
        $Sender->AddModule($ProfileModule);

        include_once(PATH_PLUGINS . DS . 'Traditional' . DS . 'class.forumstatsmodule.php');
        $ForumStatsModule = new ForumStatsModule($Sender);
        $Sender->AddModule($ForumStatsModule);
    }

    public function DiscussionsController_breadcrumbs_create($Sender) {
        //No permission check needed...anyone can view this
        // Set the model on the form.
        $Sender->Form = new Gdn_Form();
        $CategoryList = $this->GetCategoryList();
        $Sender->SetData('CategoryList', $CategoryList);

        //check home link
        if (C('Plugin.Traditional.QuickNavMainHome') == '/' || C('Plugin.Traditional.QuickNavMainHome') == '')
            $Sender->SetData('QuickNavMainHome', Anchor('Home', '/'));
        else
            $Sender->SetData('QuickNavMainHome', C('Plugin.Traditional.QuickNavMainHome'));



        $Sender->Render($this->GetView('breadcrumb.popup.php'));
    }

    private function GetCategoryList() {
        $CategoryData = CategoryModel::Categories();
        // Sanity check
        if (is_object($CategoryData))
            $CategoryData = (array) $CategoryData;
        else if (!is_array($CategoryData))
            $CategoryData = array();
        $CategoryList = array();
        //print_r($CategoryData);
        foreach ($CategoryData as $CategoryID => $Category) {
            if ($CategoryID < 0)
                continue; //no root
            if ($Category['Archived'] || !$Category['PermsDiscussionsView'])
                continue; //no archives and no categories that the user cannot see

            $Depth = GetValue('Depth', $Category, 0);
            $Name = GetValue('Name', $Category, 'Blank Category Name');
            if ($Depth > 1) {
                $Name = str_pad($Name, strlen($Name) + (($Depth - 1) * 3), ' ', STR_PAD_LEFT);
                $Name = str_replace(' ', '&#160;', $Name);
            }
            $CategoryList[] = array(
                'Name' => $Name,
                'UrlCode' => '/categories/' . $Category['UrlCode'],
                'Depth' => $Category['Depth']
            );
        }

        return $CategoryList;
    }

    public function Base_BeforeFoot_Handler($Sender) {
        echo '<div style= "clear: both"></div>';
        echo '<div id="BeforeFoot">';
        $this->CreateBreadcrumb($Sender, FALSE);
        echo '</div>';
        echo '</div>'; //need this
    }

    public function Base_AfterBody_Handler($Sender) {
        //To show num of queries and such, you have to enable the debugger database
        $Sender->AddAsset('Foot', '<h6>Page completed in ' . round(Now() - $_SERVER['REQUEST_TIME'], 4) . 's</h6>', 'PHPTimeExecution');
    }

    /**
     * Create a method called "Example" on the PluginController
     *
     * One of the most powerful tools at a plugin developer's fingertips is the ability to freely create
     * methods on other controllers, effectively extending their capabilities. This method creates the
     * Example() method on the PluginController, effectively allowing the plugin to be invoked via the
     * URL: http://www.yourforum.com/plugin/Example/
     *
     * From here, we can do whatever we like, including turning this plugin into a mini controller and
     * allowing us an easy way of creating a dashboard settings screen.
     *
     * @param $Sender Sending controller instance
     */
    public function PluginController_Traditional_Create($Sender) {
        /*
         * If you build your views properly, this will be used as the <title> for your page, and for the header
         * in the dashboard. Something like this works well: <h1><?php echo T($this->Data['Title']); ?></h1>
         */
        $Sender->Title('A Dark Traditional Theme');
        $Sender->Permission('Garden.Settings.Manage');
        $Sender->AddSideMenu('plugin/traditional'); //add the left side menu
        // If your sub-pages use forms, this is a good place to get it ready
        $Sender->Form = new Gdn_Form();
        $this->Dispatch($Sender, $Sender->RequestArgs);
    }

    public function Controller_Index($Sender) {
        // Prevent non-admins from accessing this page
        $Sender->Permission('Vanilla.Settings.Manage');
        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));

        $Validation = new Gdn_Validation();
        $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
        $ConfigurationModel->SetField(array(
            //'Plugin.Traditional.VotePluginEnable' => 'YES',
            'Plugin.Traditional.BreadcrumbHome' => 'Home',
            'Plugin.Traditional.QuickNavMainHome' => '/',
            'Plugin.Traditional.SidePanel' => FALSE,
            'Plugin.Traditional.BodyWidth' => '95%',
            'Plugin.Traditional.SubCategories' => TRUE,
            'Plugin.Traditional.SubTableHeader' => TRUE,
        ));

        // Set the model on the form.
        $Sender->Form->SetModel($ConfigurationModel);

        // If seeing the form for the first time...
        if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
            // Apply the config settings to the form.
            $Sender->Form->SetData($ConfigurationModel->Data);
        } else {
//            $ConfigurationModel->Validation->ApplyRule('Plugin.Traditional.VotePluginEnable', 'Required');
            $ConfigurationModel->Validation->ApplyRule('Plugin.Traditional.BreadcrumbHome', 'Required');
            $ConfigurationModel->Validation->ApplyRule('Plugin.Traditional.QuickNavMainHome', 'Required');
            $ConfigurationModel->Validation->ApplyRule('Plugin.Traditional.BodyWidth', 'Required');
            $Saved = $Sender->Form->Save();
            if ($Saved) {
                $Sender->StatusMessage = T("Your changes have been saved.");
            }
        }

        // GetView() looks for files inside plugins/PluginFolderName/views/ and returns their full path. Useful!
        $Sender->Render($this->GetView('traditional.php'));
    }

    /**
     * @brief adds the "Post a Reply" button as well as the page numbers
     * @param type $Sender
     */
    public function DiscussionController_BeforeDiscussion_Handler($Sender) {
        $Class = '<div class="PageNav">';
        $PageNav = FALSE;
        $String = $Class;
        //First check if user can post
        $Session = Gdn::Session();
        $HasPermission = $Session->CheckPermission('Vanilla.Discussions.Add', TRUE, 'Category', 'any');
        if ($HasPermission) {
            $PageNav = TRUE;
            $String.= '<div class="DiscussionButton">' . Anchor(T('Post a Reply'), '#Form_Body', 'TabLink') . '</div>';
        }


        if ($Session->IsValid()) {
            // Bookmark link
            $String.= Wrap(Anchor(
                            '<span>*</span>', '/vanilla/discussion/bookmark/' . $Sender->Discussion->DiscussionID . '/' . $Session->TransientKey() . '?Target=' . urlencode($Sender->SelfUrl), 'Bookmark' . ($Sender->Discussion->Bookmarked == '1' ? ' Bookmarked' : ''), array('title' => T($Sender->Discussion->Bookmarked == '1' ? 'Unbookmark' : 'Bookmark')), 'span', array('class' => 'BookMark'))
            );
        }


        if ($Sender->Pager->LastPage()) {
            $LastCommentID = $Sender->AddDefinition('LastCommentID');
            if (!$LastCommentID || $Sender->Data['Discussion']->LastCommentID > $LastCommentID)
                $Sender->AddDefinition('LastCommentID', (int) $Sender->Data['Discussion']->LastCommentID);
            $Sender->AddDefinition('Vanilla_Comments_AutoRefresh', Gdn::Config('Vanilla.Comments.AutoRefresh', 0));
        }
        $pages = $Sender->Pager->ToString('more');
        if ($pages != '') {
            $PageNav = TRUE;
            $String .= $pages;
        }

        if ($PageNav) {
            echo $String;
            echo '</div>';
        }
        echo '<div style="clear: both"></div>'; //Important!! Leave this here!!
    }

    //Add the theme switcher
    public function ProfileController_AfterAddSideMenu_Handler($Sender) {

        $SideMenu = $Sender->EventArguments['SideMenu'];
        $Session = Gdn::Session();
        $ViewingUserID = $Session->UserID;

        if ($Sender->User->UserID == $ViewingUserID) {
            $SideMenu->AddLink('Options', T('Theme Switcher'), '/profile/theme', FALSE, array('class' => 'Popup'));
        }
    }

    public function ProfileController_Theme_Create($Sender) {
        echo '<div style="width: 300px; height: 300px">coming soon</div>';
    }

    public function DiscussionController_AfterMessageMeta_Handler($Sender) {
        $offset = $Sender->Pager->Offset;
        $count = $this->count++;
        echo '<span class="DiscussionCount"> Post: ' . ($this->count + $offset) . '</span>';
    }

    /**
     * badly needs a cleanup
     * @param type $Sender
     * @return type
     */
    private function MenuBanner($Sender) {
        //echo substr(xx) delete this in previous version -JJB
        ob_start();
        ?>
        <div id="MenuBanner">
            <ul>
                <li<?php echo ($Sender->SelfUrl != 'activity' && !((strpos($Sender->SelfUrl, 'discussions/p') !== FALSE) || $Sender->SelfUrl === 'discussions')) ? ' class="Active"' : ''; ?>><?php echo Anchor('Forum', '/') ?></li>
                <li<?php echo strtolower($Sender->SelfUrl) == 'activity' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('Recent Activity'), 'activity'); ?></li>
                <li<?php echo ((strpos($Sender->SelfUrl, 'discussions/p') !== FALSE) || $Sender->SelfUrl === 'discussions') ? ' class="Active"' : ''; ?>><?php echo Anchor(T("What's New?"), 'discussions'); ?></li>
                <li><?php //echo Anchor(T('Members'), '/') //coming soon ?></li>
            </ul>
        </div>
        <?php
        $String = ob_get_contents();
        @ob_end_clean();
        return $String;
    }

    private function SetBreadcrumbs($Sender, $Breadcrumbs, $ShowSearch = FALSE) {
        if ($ShowSearch)
            echo self::MenuBanner($Sender);
        ob_start();
        ?>

        <div <?php echo (($Sender->SelfUrl != 'activity' && !((strpos($Sender->SelfUrl, 'discussions/p') !== FALSE) || $Sender->SelfUrl === 'discussions')) || $ShowSearch == FALSE) ? 'class ="BreadcrumbsContainer"  ' : ' class ="BreadcrumbsContainer  BreadRound"' ?>>
            <div class="Breadcrumbs">
                <?php if (($Sender->SelfUrl != 'activity' && !((strpos($Sender->SelfUrl, 'discussions/p') !== FALSE) || $Sender->SelfUrl === 'discussions')) || $ShowSearch == FALSE): ?>
                    <?php echo Anchor('', ' ', array('class' => 'Home')); ?>
                    <?php echo Gdn_Theme::Breadcrumbs($Breadcrumbs, $Format = '<a href="{Url,html}">{Name,html}</a>', FALSE); ?>
        <?php else: ?>
                    <ul id="js-news" class="js-hidden">
                    </ul>
                <?php endif ?>
        <?php if ($ShowSearch) : ?>

                    <div class="SearchHeader">
                        <?php
                        $Form = Gdn::Factory('Form');
                        $Form->InputPrefix = '';
                        echo $Form->Open(array('action' => Url('/search'), 'method' => 'get'));
                        echo $Form->TextBox('Search', array('size' => 34, 'class' => 'defaultText SearchBox', 'title' => T('Search', 'Search')));
                        ?>
                        <span> <?php echo Anchor('', 'discussions/breadcrumbs', array('class' => 'QuickNavImage Popup')) ?></span>
                        <div class="ImageContainer"><input type="submit" value="" class="SearchFinderImage"/></div>
                    </div>
                    </form>
                </div>
            </div>
        <?php endif ?>


        <div style="clear: both"></div>
        </div>
        <?php
        $String = ob_get_contents();
        @ob_end_clean();
        echo $String;
    }

    /**
     * @brief Used in the master tempate to always show breadcrumbs on every page
     * @param object Sender
     */
    public function CreateBreadcrumb($Sender, $ShowSearch) {
        if (1) { // $Sender->SelfUrl != 'categories/all' no breadcrumbs for main page
            if ($Sender->ControllerName == 'profilecontroller') {
                //print_r($Sender); die;
                $username = explode('/', $Sender->SelfUrl); //get username to load
                $username = $Sender->User->Name;
                $Breadcrumbs = array(
                    array('Name' => C('Plugin.Traditional.BreadcrumbHome', 'Home'),
                        'Url' => '/'
                    ),
                    array(
                        'Name' => $username,
                        'Url' => $Sender->SelfUrl
                    ),
                );
            } else {
                $Breadcrumbs = Gdn::Controller()->Data('Breadcrumbs');

                if (empty($Breadcrumbs)) {
                    $Breadcrumbs = array(//create the new breadcrumb trail
                        array(
                            'Name' => C('Plugin.Traditional.BreadcrumbHome', 'Home'),
                            'Url' => '/'
                        ),
                    );
                } else {
                    array_unshift($Breadcrumbs, array(//INstead of the generic "home" link, add in our own to the top of the array
                        'Name' => C('Plugin.Traditional.BreadcrumbHome', 'Home'),
                        'Url' => '/'
                    ));
                }
                if ($Sender->ControllerName == 'discussioncontroller') { //Add link to the current thread
                    $Breadcrumbs[] =
                            array(
                                'Name' => $Sender->Discussion->Name,
                                'Url' => '/discussion/' . $Sender->Discussion->DiscussionID . '/' . Gdn_Format::Url($Sender->Discussion->Name) . '/p1'//add p1 so that if click, will bring to topic starter and not latest in the page
                    );
                }
            }
            self::SetBreadcrumbs($Sender, $Breadcrumbs, $ShowSearch);
        }
    }

    /**
     * @brief Add a "New Thread" button to catagories with no children
     * @param object $Sender
     */
    public function CategoriesController_AfterDiscussionTabs_Handler($Sender) {

        //First check if the category allows new posts
        $HasPermission = Gdn::Session()->CheckPermission('Vanilla.Discussions.Add', TRUE, 'Category', 'any');
        if ($HasPermission) {
            $CatID = GetValue('CategoryID', $Sender->Category);
            echo Wrap(Anchor(T('New Thread'), '/post/discussion/' . $CatID, 'PopularDiscussions'), 'li', array('style' => 'float: left;margin-top: 3px'));
        }
    }

    /**
     * @brief Decides whether or not to load single discussion or all discussion view based on # of children
     * @param object $Sender
     */
    public function CategoriesController_BeforeGetDiscussions_Handler($Sender) {
        //print_r($Sender->Category); die;
        $Category = GetValue('Category', $Sender);
        //print_r($Cat); die;


        if ($Category->Depth != 0 && (sizeof($Category->ChildIDs) > 0)) {
            if ($Category->Depth == 1) {
                $Category->SubCategory = 1;
                $Sender->ControllerName = 'categoriescontroller';
                $Sender->View = 'all';
                $Sender->All();             //always show root category children
                die; //stop execution so vanilla does not render multiple times upon exit of this method
            } else if (C('Plugin.Traditional.SubCategories', TRUE)) {
                $Category->SubCategory = 1;
                $Sender->ControllerName = 'categoriescontroller';
                $Sender->View = 'all';
                $Sender->All();
                die; //stop execution so vanilla does not render multiple times upon exit of this method
            }
        }
    }

    //Is called directly from view
    public function CreatePager($DiscussionID, $Sender) {
        if ($Sender->ControllerName != 'profilecontroller') { //so it doesn't load the pager in the profile
            //print_r($Sender); die;
            //print_r($Sender->DiscussionData->Result()); die;
            $DiscussionModel = new DiscussionModel();

            $Sender->SetData('Discussion', $DiscussionModel->GetID($DiscussionID), TRUE); //create the 'Discussion' object
            //print_r($Sender->Discussion); die;
            // Actual number of comments, excluding the discussion itself
            $ActualResponses = $Sender->Discussion->CountComments - 1;
            // Define the query offset & limit
            $Limit = C('Vanilla.Comments.PerPage', 50);
            $OffsetProvided = $Offset = 0;

            list($Offset, $Limit) = OffsetLimit($Offset, $Limit);


            $Sender->Offset = $Offset;
            if (C('Vanilla.Comments.AutoOffset')) {
                if ($Sender->Discussion->CountCommentWatch > 0 && $OffsetProvided == '')
                    $Sender->AddDefinition('LocationHash', '#Item_' . $Sender->Discussion->CountCommentWatch);

                if (!is_numeric($Sender->Offset) || $Sender->Offset < 0 || !$OffsetProvided) {
                    // Round down to the appropriate offset based on the user's read comments & comments per page
                    $CountCommentWatch = $Sender->Discussion->CountCommentWatch > 0 ? $Sender->Discussion->CountCommentWatch : 0;
                    if ($CountCommentWatch > $ActualResponses)
                        $CountCommentWatch = $ActualResponses;

                    // (((67 comments / 10 perpage) = 6.7) rounded down = 6) * 10 perpage = offset 60;
                    $Sender->Offset = floor($CountCommentWatch / $Limit) * $Limit;
                }
                if ($ActualResponses <= $Limit)
                    $Sender->Offset = 0;

                if ($Sender->Offset == $ActualResponses)
                    $Sender->Offset -= $Limit;
            } else {
                if ($Sender->Offset == '')
                    $Sender->Offset = 0;
            }

            if ($Sender->Offset < 0)
                $Sender->Offset = 0;

            ///@todo fix this hack
            $Sender->Offset = 9999999; ///hack to never include the "Highlight" class on page number
            // Set the canonical url to have the proper page title.
            $Sender->CanonicalUrl(Url(ConcatSep('/', 'discussion/' . $Sender->Discussion->DiscussionID . '/' . Gdn_Format::Url($Sender->Discussion->Name), PageNumber($Sender->Offset, $Limit, TRUE)), TRUE));

            // Load the comments
//      $Sender->SetData('CommentData', $Sender->CommentModel->Get($DiscussionID, $Limit, $Sender->Offset), TRUE);
//      $Sender->SetData('Comments', $Sender->CommentData);
//
//      // Make sure to set the user's discussion watch records
//      $Sender->CommentModel->SetWatch($Sender->Discussion, $Limit, $Sender->Offset, $Sender->Discussion->CountComments);
            // Build a pager
            $PagerFactory = new Gdn_PagerFactory();
            $Sender->EventArguments['PagerType'] = 'Pager';
            $Sender->FireEvent('BeforeBuildPager');
            $Sender->Pager = $PagerFactory->GetPager($Sender->EventArguments['PagerType'], $Sender);
            $Sender->Pager->ClientID = 'Pager';

            $Sender->Pager->Configure(
                    $Sender->Offset, $Limit, $ActualResponses, 'discussion/' . $DiscussionID . '/' . Gdn_Format::Url($Sender->Discussion->Name) . '/%1$s'
            );
        }
    }

    public function CategoriesController_BeforeCategoryItem_Handler($Sender) {
        $CssClasses = '';
        $Category = GetValue('Category', $Sender->EventArguments);
        $CatList = '';
        $Children = '';
        $Category->LatestPost = '';
        $LatestPostInfo = '';
        $CatID = $Category->CategoryID;
        $Once = FALSE;


        if ($Category->Depth > 0 && $Sender->SelfUrl == 'categories/all') {
            if ($Category->Depth == 2)
                $LatestPostInfo = (object) $this->_GetLatestPost($CatID);
            foreach ($Sender->CategoryData->Result() as $Item) {
                if ($Item->ParentCategoryID == $CatID) {
                    if ($CatList == '') {
                        $CatList = '<span class="ChildCategories">' . Wrap(T('Child Categories: '), 'b');
                        $CatList.= Anchor(Gdn_Format::Text($Item->Name), '/categories/' . $Item->UrlCode);
                    } else {
                        $CatList .= ', ';
                        $CatList.= Anchor(Gdn_Format::Text($Item->Name), '/categories/' . $Item->UrlCode);
                    }
                }
            }
            $CatList.='</span>';
            $Sender->EventArguments['ChildCategories'] = $CatList;
            //print_r($LatestPostInfo);
            if (isset($LatestPostInfo->LastDiscussionTitle)) { //will include the thread just be starting and any latest posts
                $Last = UserBuilder($LatestPostInfo, 'Last');
                //print_r($Last);
                //print_r($LatestPostInfo); die;
                $LatestPost =
                        '<div class="LatestPostWrap">'
                        . UserPhoto($Last, array('LinkClass' => 'ProfilePhotoCategory', 'ImageClass' => 'ProfilePhotoSmall'))//Img(Gdn_Upload::Url(ChangeBasename($Last->Photo, 'n%s')), array('class' => 'ProfilePhotoCategory', 'width' => 24, 'height' => 24))
                        . Wrap(Anchor(SliceString($LatestPostInfo->LastDiscussionTitle, 40), '/discussion/' . $LatestPostInfo->LastDiscussionID . '/' . Gdn_Format::Url($LatestPostInfo->LastDiscussionTitle)), 'span', array('class' => 'LastDiscussionTitle'))
                        . '<br/>'
                        . '<span class="LastAuthor">' . UserAnchor($Last) . '</span>'
                        . '<span class="LastCommentDate">' . Gdn_Format::Date($LatestPostInfo->DateLastComment) . '</span>'
                        . '</div>'

                ;
                $Category->LatestPost = $LatestPost;
            }
            else
                $Category->LatestPost = '';
        } else { //don't enter if loading all catagories
            $LatestPost = '';
            if ($Category->Depth > 0) {
                $UrlCode = str_replace('categories/', '', $Sender->SelfUrl);
                //echo $UrlCode; die;
                //print_r($Category); die;
                if ($Category->UrlCode == $UrlCode) { //make as header
                    $CatList .= '<li class="Category-' . $Category->UrlCode . ' ' . $CssClasses . '">
               <div class="CatHeaders">' . Anchor(Gdn_Format::Text($Category->Name), '/categories/' . $Category->UrlCode, 'Title') . '</div>'
                            . '
            </li>';
                    foreach ($Sender->CategoryData->Result() as $Item) {
                        if ($Item->ParentCategoryID == $CatID) {
                            foreach ($Sender->CategoryData->Result() as $InnerItem) {
                                if ($InnerItem->ParentCategoryID == $Item->CategoryID) {
                                    if ($Children == '') {
                                        $Children = '<span class="ChildCategories">' . Wrap(T('Child Categories: '), 'b');
                                        $Children.= Anchor(Gdn_Format::Text($InnerItem->Name), '/categories/' . $InnerItem->UrlCode);
                                    } else {
                                        $Children .= ', ';
                                        $Children.= Anchor(Gdn_Format::Text($InnerItem->Name), '/categories/' . $InnerItem->UrlCode);
                                    }
                                }
                            }
                            if ($Children != '')
                                $Children.='</span>';
                            $LatestPostInfo = (object) $this->_GetLatestPost($Item->CategoryID);
                            //print_r($LatestPostInfo);
                            //same as above
                            if (isset($LatestPostInfo->LastDiscussionTitle)) {
                                $Last = UserBuilder($LatestPostInfo, 'Last');
                                //print_r($LatestPostInfo); die;
                                $LatestPost =
                                        '<div class="LatestPostWrap">'
                                        . UserPhoto($Last, array('LinkClass' => 'ProfilePhotoCategory', 'ImageClass' => 'ProfilePhotoSmall'))
                                        . '<span class="LastDiscussionTitle">' . Anchor($LatestPostInfo->LastDiscussionTitle, '/discussion/' . $LatestPostInfo->LastDiscussionID . '/' . Gdn_Format::Url($LatestPostInfo->LastDiscussionTitle)) . '</span>'
                                        . '<br/>'
                                        . '<span class="LastAuthor">' . UserAnchor($Last) . '</span>'
                                        . '<span class="LastCommentDate AllCatListing">' . Gdn_Format::Date($LatestPostInfo->DateLastComment) . '</span>'
                                        . GetOptions($Item, $Sender)
                                        . '</div>'

                                ;
                            } else {
                                $LatestPost = '';
                            }
                            if ($Once == FALSE)
                                echo '<ul class="DataList CategoryList' . (1 ? ' CategoryListWithHeadings' : '') . '">'; $Once = TRUE;
                            $Item->Depth = 1;
                            $AltCss = '';
                            $CssClasses = '';

                            $CatList .= '<li class="Item Depth' . $Item->Depth . $AltCss . ' Category-' . $Item->UrlCode . ' ' . $CssClasses . '">
                <table class="AllCat">
                <tbody>
                 <tr>
                    <td class="CategoryName">
                    <span class="CategoryTitle">' . Anchor(Gdn_Format::Text($Item->Name), '/categories/' . $Item->UrlCode, 'Title') . '</span>
                        <span class="CategoryDesc">' . $Item->Description . '</span>
                         ' . $Children . '
                    </td>

                    <td class="LatestPost">' . $LatestPost . '</td>
                    <td class="ThreadCount">' . Gdn_Format::BigNumber($Item->CountAllDiscussions, 'html') . '</td>
                    <td class="PostCount"> ' . Gdn_Format::BigNumber($Item->CountAllComments, 'html') . '</td>
                </tr>
                </tbody>
                </table>'
                            ;



                            $CatList .= '
            </li>';
                            $Children = ''; //reset
                        }
                    }
                    echo $CatList;
                }
            }
        }
    }

    public function PostController_CategoryDropDown_Handler($Sender) {
        $Form = new PostEdits();
        echo $Form->CategoryDropDown('CategoryID', array('Value' => GetValue('CategoryID', $Sender->Category)));
    }

    /**
     * @brief Get children of the @param $CatID
     * @param array $CatID
     * @return array
     */
    private function _GetChildIDs($CatID) {
        $ID_Array = array();
        $DiscussionModel = new DiscussionModel();

        /**
         * @todo don't select any threads that are sunken or closed
         */
        $IDs = $DiscussionModel->SQL
                        ->Select('CategoryID')
                        ->From('Category')
                        ->WhereIn('ParentCategoryID', $CatID)
                        ->Get()->ResultArray();
        if (empty($IDs))
            return FALSE;
        else {
            foreach ($IDs as $ID) {
                array_push($ID_Array, $ID['CategoryID']);
            }
            return $ID_Array;
        }
    }

    private function _GetLatestPost($CategoryID) {
        $DiscussionModel = new DiscussionModel();
        //$CategoryModel = new CategoryModel();
        $ChildTemp = array();

        //Get first level
        $ChildrenMasterList = $this->_GetChildIDs(array($CategoryID));
        //print_r($ChildrenMasterList);die;
        //Now get the children of children
        if (!empty($ChildrenMasterList)) {
            $ChildsIDs = TRUE;
            $ChildTemp = $ChildrenMasterList;
            while (1) {
                $ChildsIDs = $this->_GetChildIDs($ChildTemp);
                if ($ChildsIDs == FALSE)
                    break;
                $ChildTemp = array(); //clear temporary
                foreach ($ChildsIDs as $ID) { //these *SHOULD* all be unique anyways
                    array_push($ChildrenMasterList, $ID); //join all catagory IDs which are related to one another in master array
                    array_push($ChildTemp, $ID); //make temporary to search through again
                }
            }
        }
        else
            $ChildrenMasterList = array($CategoryID);

        //print_r($ChildrenMasterList);
        //Now that all Category IDs are collected, the latest post of all
        //of the categories can be obtained
        ///@todo join this into one query where it first checks if d.LastCommentUserID exists
        //$DiscussionModel->Reset();
        $LatestPostInfo = $DiscussionModel->SQL
                        ->Select('u.Name as LastName,
                            u.UserID as LastUserID,
                            u.Photo as LastPhoto,
                            u.Email as LastEmail,
                            u.Gender as LastGender,
                            d.Name as LastDiscussionTitle,
                            d.LastCommentUserID as LastCommentID,
                            d.InsertUserID,d.DateLastComment,
                            d.DiscussionID as LastDiscussionID'
                        ) //get original poster in case no added comments
                        ->From('Discussion d')
                        ->Join('User u', 'u.UserID = d.LastCommentUserID', 'inner')
                        ->WhereIn('d.CategoryID', $ChildrenMasterList)
                        ->OrderBy('d.DateLastComment', 'desc') //DateLastComment == DateInserted if no comments yet
                        ->Limit('1', 0)
                        ->Get()->ResultArray();
        //print_r($LatestPostInfo);

        if (empty($LatestPostInfo)) {
            //Get here if no posts...now check if any threads with no recent comments
            $LatestPostInfo = $DiscussionModel->SQL
                            ->Select('u.Name as LastName,
                            u.UserID as LastUserID,
                            u.Photo as LastPhoto,
                            u.Email as LastEmail,
                            u.Gender as LastGender,
                            d.Name as LastDiscussionTitle,
                            d.LastCommentUserID as LastCommentID,
                            d.InsertUserID,d.DateLastComment,
                            d.DiscussionID as LastDiscussionID'
                            ) //get original poster in case no added comments
                            ->From('Discussion d')
                            ->Join('User u', 'u.UserID = d.InsertUserID', 'inner')
                            ->WhereIn('d.CategoryID', $ChildrenMasterList)
                            ->OrderBy('d.DateLastComment', 'desc') //DateLastComment == DateInserted if no comments yet
                            ->Limit('1', 0)
                            ->Get()->ResultArray();
            if (empty($LatestPostInfo))
                return FALSE;
            else
                return $LatestPostInfo[0];
        }
        else
            return $LatestPostInfo[0];
    }

    private function _SearchPagerCandidate($Pager) {
        $doc = new DOMDocument("1.0", "utf-8");
        $doc->loadHTML($Pager);
        $elements = $doc->getElementsByTagName('span');
        $i = 0;
        foreach ($elements as $param) {
            $node = $elements->item($i)->getAttribute('class');
            if ($node == 'Ellipsis') {
                return TRUE; //Found
            }
            $i++;
        }
        return FALSE; //NOT FOUND
    }

    private function _CreateGotoPager($Pager, $Url, $DiscussionID) {
        if ($this->_SearchPagerCandidate($Pager)) {
            $DownPager = Anchor('&nbsp&nbsp', $Url . '#', array('class' => 'GotoPageLink', 'name' => 'ID_' . $DiscussionID)); //link to nowhere
        }
        else
            $DownPager = '';
        return $DownPager;
    }

    public function DiscussionController_test_Create($Sender) {
        $PageNum = filter_input(INPUT_GET, "Page", FILTER_VALIDATE_INT); //validate
        $MaxPage = filter_input(INPUT_GET, "MaxPage", FILTER_VALIDATE_INT); //validate
        //Validate
        if (!is_numeric($PageNum) || $PageNum == '')
            $PageNum = 1; //default to first page
        if (!is_numeric($MaxPage) || $MaxPage == '')
            $MaxPage = 1;

        if ($PageNum > $MaxPage || $PageNum < 0) //don't allow eronous page numbers
            $PageNum = 'p' . $MaxPage;
        else
            $PageNum = 'p' . $PageNum;

        $Segments = explode('/', $Sender->SelfUrl);


        header('Location:' . Gdn::Request()->Url('discussion/' . $Segments[2] . '/' . $Segments[3] . '/' . $PageNum)); //redirect
        exit; //stop execution
    }

    /**
     * Add Body text
     * @param object $Sender
     */
    public function CategoriesController_AfterDiscussionTitle_Handler($Sender) {
        $Pages = $Sender->Pager->ToString('more');
        if ($Pages) {
            $DiscussionID = $Sender->Category->LastDiscussionID;
            $Url = $Sender->Category->Url;
            $DiscussionUrl = 'discussion/test/' . $Sender->EventArguments['Discussion']->DiscussionID . '/' . Gdn_Format::Url($Sender->EventArguments['Discussion']->Name); //safe url
            //Get the max page number to trim if user inputs a higher value (this saves the unecessary disk seeks)
            $Limit = C('Vanilla.Comments.PerPage', 50);
            $CountCommentWatch = $Sender->EventArguments['Discussion']->CountComments > 0 ? $Sender->EventArguments['Discussion']->CountComments : 0;
            $MaxPage = floor($CountCommentWatch / $Limit);

            $DownPager = $this->_CreateGotoPager($Pages, $Url, $DiscussionID);
            $Pages = substr($Pages, 0, -12);
            $Pages = $Pages . $DownPager . '</div>'; //stick in the downpager into the Pages <div>
            $Hidden = '<div class="GotoPageLinkPopDown" id="ID_' . $DiscussionID . '">';

            $Hidden .= '<form action="' . Url($DiscussionUrl) . '" method="get" name="blah">'; //carefull...need to use the "Url" function for server modrewrites
            $Hidden.='<span class="SubHiddenPager">Go To Page: </span>';
            $Hidden.='<input type="text" value="" name="Page" />';
            $Hidden .='<input type="hidden" value="' . $MaxPage . '" name="MaxPage">'; //max page to reach
            $Hidden.='</form></div></div>';
            $Pages = $Pages . $Hidden;
        }
        echo $Pages;
    }

    /**
     * This deletes the Guest module from displaying in the profile view
     * and puts in the default photo pic if none is uploaded
     * @param object $Sender
     */
    public function profile(&$Sender) {
        unset($Sender->Assets['Panel']['GuestModule']);     //delete "your new here" assest

        if (!isset($Sender->Assets['Panel']['UserPhotoModule']->User->Photo)) {
            $Builder = UserBuilder($Sender->Assets['Panel']['UserPhotoModule']->User);
            if (isset($Builder->Photo)) {
                $Builder->Photo = Gdn_Upload::Url(ChangeBasename($Builder->Photo, 'p%s'));
            }
            //Hack around the default size for Gravatar...hopefully this gets fixed in the core
            $DefaultWidth = C('Garden.Thumbnail.Width', 50);
            SaveToConfig('Garden.Thumbnail.Width', 250);
            echo UserPhoto($Builder, array('LinkClass' => 'ProfileMainImage', 'ImageClass' => 'ProfileMainImage'));
            SaveToConfig('Garden.Thumbnail.Width', $DefaultWidth);
        }

        echo $Sender->RenderAsset('Panel');
    }

    public function GeneratePanel($Sender) {
        $ControllerList = array(//list of controllers to NOT allow the view of the panel
            'profilecontroller',
            'discussioncontroller',
            'searchcontroller',
        );
        //print_r($this->Assets['Panel']); die;
        if (!in_array(strtolower($Sender->ControllerName), $ControllerList)) {
            //unset($this->Assets['Panel']['GuestModule']);
            unset($this->Assets['Panel']['BookmarkedModule']);
            unset($this->Assets['Panel']['NewDiscussionModule']);
            unset($this->Assets['Panel']['CategoryFollowToggleModule']);

            if (C('Plugin.Traditional.SidePanel', TRUE) || $Sender->ControllerName == 'messagescontroller') { //always alllow panel in conversations
                echo '<div id="PanelHolder">';
                echo '<div id="Panel">';
                echo $Sender->RenderAsset('Panel');
                echo '</div></div>';
            }
        }
    }

    /*     * ******************************ProfileModule******************************* */

    public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {
        $Menu = $Sender->EventArguments['SideMenu'];
        $Menu->AddLink('Add-ons', 'Profile Panel', 'plugin/mcu', 'Garden.Themes.Manage');
    }

    public function Setup() {

        // Set up the plugin's default values
        SaveToConfig('Plugin.Traditional.VotePluginEnable', FALSE);
        SaveToConfig('Plugin.Traditional.BreadcrumbHome', 'Home');
        SaveToConfig('Plugin.Traditional.QuickNavMainHome', '/');
        SaveToConfig('Plugin.Traditional.SidePanel', FALSE);
        SaveToConfig('Plugin.Traditional.BodyWidth', '95%');
        SaveToConfig('Plugin.Traditional.SubCategories', TRUE);
        SaveToConfig('Plugin.Traditional.SubTableHeader', TRUE);
        SaveToConfig('Plugin.Traditional.ForumTitle', 'My Forum Title');
        SaveToConfig('Plugin.Traditional.ForumDescription', 'My Forum Description');
    }

    public function OnDisable() {
        RemoveFromConfig('Plugin.Traditional.VotePluginEnable');
        RemoveFromConfig('Plugin.Traditional.BreadcrumbHome');
        RemoveFromConfig('Plugin.Traditional.QuickNavMainHome');
        RemoveFromConfig('Plugin.Traditional.SidePanel');
        RemoveFromConfig('Plugin.Traditional.BodyWidth');
        RemoveFromConfig('Plugin.Traditional.SubCategories');
        RemoveFromConfig('Plugin.Traditional.SubTableHeader');
        RemoveFromConfig('Plugin.Traditional.ForumTitle');
        RemoveFromConfig('Plugin.Traditional.FormDescription');
    }

}

