<?PHP

if($member_db[UDB_ACL] > ACL_LEVEL_EDITOR)
    msg("error", "Access Denied", "You don't have permission to edit comments");

// ********************************************************************************
// Edit Comment
// ********************************************************************************
if($action == "editcomment")
{
    if ($source == "")
         $all_comments = file(SERVDIR."/cdata/comments.txt");
    else $all_comments = file(SERVDIR."/cdata/archives/$source.comments.arch");

    foreach ($all_comments as $comment_line)
    {
        $comment_line_arr = explode("|>|", $comment_line);
        if ($comment_line_arr[0] == $newsid)
        {
            $comment_arr = explode("||", $comment_line_arr[1]);
            foreach($comment_arr as $single_comment)
            {
                $single_arr = explode("|", $single_comment);
                if ($comid == $single_arr[0]) break;
            }
        }
    }

    $single_arr[4] = str_replace("<br />", "\n", $single_arr[4]);
    $comdate       = date("D, d F Y h:i:s", $single_arr[0]);

    header('Content-Type: text/html; charset=UTF-8');
    echo proc_tpl('editcomments', array(
        'newsid'        => $newsid,
        'comid'         => $comid,
        'comdate'       => $comdate,
        'source'        => $source,
        'single_arr[1]' => $single_arr[1],
        'single_arr[2]' => $single_arr[2],
        'single_arr[3]' => $single_arr[3],
        'single_arr[4]' => $single_arr[4],
    ));

}
// ********************************************************************************
// Do Save Comment
// ********************************************************************************
elseif($action == "doeditcomment")
{
    if (empty($poster) and empty($deletecomment))
    {
        echo lang("The poster can not be blank");
        exit_cookie();
    }

    if (empty($mail))   $mail   = lang("none");
    if (empty($poster)) $poster = lang("Anonymous");
    if (empty($comment) && isset($comment)) die(lang("Comment can not be blank"));

    $comment = str_replace(array("\r","\t"), " ", $comment);
    $comment = str_replace("\n", "<br />", $comment);
    $comment = str_replace("|", "I", $comment);

    if (empty($source))
    {
        $news_file  = SERVDIR."/cdata/news.txt";
        $com_file   = SERVDIR."/cdata/comments.txt";
    }
    else
    {
        $news_file  = SERVDIR."/cdata/archives/$source.news.arch";
        $com_file   = SERVDIR."/cdata/archives/$source.comments.arch";
    }

    $old_com = file( $com_file);
    $new_com = fopen( $com_file, "w");

    foreach ($old_com as $line)
    {
        $line_arr = explode("|>|",$line);
        if ($line_arr[0] == $newsid)
        {
            fwrite($new_com, $line_arr[0]."|>|");
            $comments = explode("||", $line_arr[1]);
            foreach($comments as $single_comment)
            {
                $single_comment = trim($single_comment);
                $comment_arr = explode("|", $single_comment);
                if ($comment_arr[0] == $comid and !empty($comment_arr[0]) and $delcomid != "all")
                    fwrite($new_com,"$comment_arr[0]|$poster|$mail|$comment_arr[3]|$comment||");

                elseif ($delcomid[$comment_arr[0]] != 1 and !empty($comment_arr[0]) and $delcomid['all'] != 1)
                    fwrite($new_com,"$single_comment||");
            }
            fwrite($new_com,"\n");
        }
        else fwrite($new_com, $line);
     }

     if (isset($deletecomment) and $delcomid['all'] == 1)
         msg("info", lang("Comments Deleted"), lang("All comments were deleted"), "$PHP_SELF?mod=editnews&action=editnews&id=$newsid&source=$source");

     elseif (isset($deletecomment) and isset($delcomid))
         msg("info", lang("Comment Deleted"), lang("The selected comment(s) has been deleted"), "$PHP_SELF?mod=editnews&action=editnews&id=$newsid&source=$source");

     else echo "<b>".lang('Comment is saved!')."</b>";
}

?>