<?php

class ForumStatsModule extends Gdn_Module {

    public $ForumStats1 = '';
    public $ForumStats2 = '';

    public function AssetTarget() {
        return 'Panel';
    }

    public function GetStats() {
        $UserModel = Gdn::UserModel();

        // Load some data to display on the dashboard
        $this->ForumStats1 = array();
        $this->ForumStats2 = array();
        // Get the number of users in the database
        $CountUsers = $UserModel->GetCountLike();
        $this->ForumStats1[T('Members')] = Gdn_Format::BigNumber($CountUsers);
        // Get the number of new users in the last month
        $this->ForumStats2[T('Members Past week')] = Gdn_Format::BigNumber(
                        $UserModel->GetCountWhere(array('DateInserted >=' => Gdn_Format::ToDateTime(strtotime('-1 week'))))
        );
        $this->ForumStats3[T('Members Past month')] = Gdn_Format::BigNumber(
                        $UserModel->GetCountWhere(array('DateInserted >=' => Gdn_Format::ToDateTime(strtotime('-1 month'))))
        );

        $DiscussionModel = new DiscussionModel();
        // Number of Discussions
        $CountDiscussions = $DiscussionModel->GetCount();
        $this->ForumStats1[T('Threads')] = Gdn_Format::BigNumber($CountDiscussions);
        // Number of New Discussions in the last month
        $this->ForumStats2[T('Threads Past week')] = number_format($DiscussionModel->GetCount(array('d.DateInserted >=' => Gdn_Format::ToDateTime(strtotime('-1 week')))));
        $this->ForumStats3[T('Threads Past month')] = number_format($DiscussionModel->GetCount(array('d.DateInserted >=' => Gdn_Format::ToDateTime(strtotime('-1 month')))));



        $CommentModel = new CommentModel();
        // Number of Comments
        $CountComments = $CommentModel->GetCountWhere();
        $this->ForumStats1[T('Messages')] = Gdn_Format::BigNumber($CountComments);
        // Number of New Comments in the last week
        $this->ForumStats2[T('Messages Past week')] = number_format($CommentModel->GetCountWhere(array('DateInserted >=' => Gdn_Format::ToDateTime(strtotime('-1 week')))));
        $this->ForumStats3[T('Messages Past month')] = number_format($CommentModel->GetCountWhere(array('DateInserted >=' => Gdn_Format::ToDateTime(strtotime('-1 month')))));


        // Get recently active users
        //$this->ForumStats = $UserModel->GetActiveUsers(5);
    }

    public function ToString() {

        if (C('Plugin.Traditional.SidePanel', TRUE)) {
            $this->GetStats();
            //print_r($this->ForumStats); die;

            ob_start();
            ?>
            <div class="Header">
                <h5>Forum Stats </h5>
            </div>
            <div id="ForumStatsBox" class="Box">
                <table class="TableBox">
                    <tbody>
                        <tr>
                            <td class="CurrentTableHead">
                                Current
                            </td>
                            <td class="CurrentNumbersHead">

                            </td>
                            <td class="WeekTableHead">
                                Week
                            </td>
                            <td class="MonthTableHead">
                                Month
                            </td>
                        </tr>
                        <tr>
                            <td class="Current">
                                <ul>
                                    <?php foreach ($this->ForumStats1 as $Name => $Value) : ?>
                                        <?php if (1)://could check if >0 here : ?>
                                            <li>
                                                <span class="Label"><?php echo $Name ?>:</span>
                                            </li>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </ul>
                            </td>
                            <td class="CurrentNumbers">
                                <ul>
                                    <?php foreach ($this->ForumStats1 as $Name => $Value) : ?>
                                        <?php if (1)://could check if >0 here : ?>
                                            <li>
                                                <span class="Value"><?php echo Gdn_Format::BigNumber($Value) ?></span>
                                            </li>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </ul>
                            </td>
                            <td class="Week">
                                <ul>
                                    <?php foreach ($this->ForumStats2 as $Name => $Value) : ?>
                                        <?php if (1)://could check if >0 here : ?>

                                            <li>
                                                <?php echo Gdn_Format::BigNumber($Value) ?>
                                            </li>
                                        <?php endif ?>
                                    <?php endforeach ?>



                                </ul>
                            </td>
                            <td class="Month">
                                <ul>
                                    <?php foreach ($this->ForumStats3 as $Name => $Value) : ?>
                                        <?php if (1)://could check if >0 here : ?>

                                            <li>
                                                <?php echo Gdn_Format::BigNumber($Value) ?>
                                            </li>
                                        <?php endif ?>
                                    <?php endforeach ?>



                                </ul>
                            </td>



                        </tr>
                    </tbody>
                </table>
                <div style="clear: both"></div>
            </div>
            <?php
        }
    }

}