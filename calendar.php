<?php
function display_calendar()
{
    $html='<div class="calendar"><div class="header"><div class="month-year">';
    $html.=date("Y")."年".date("m")."月";
    $html.='</div></div>';
    $html.='<div class="days">'.displayweek().'</div>';//顯示星期
    $html.='</div><br>';
    $html.='<div><div>木棧道開放及關閉時間，仍需以現場公布為準。</div>
    <div align="right"><a href="https://nelsonprogram.blogspot.com/2022/11/blog-post.html" target="_blank">
    >>意見反映<<</a>　　　訪客數：'.counter().'</div></div>';
    return $html;
}
function displayweek()
{
    $html='';
    $weekarray=array("日","一","二","三","四","五","六");
    foreach ($weekarray as $day) 
        $html.='<div class="day_name">星期'.$day."</div>";
    $html.="\n";    
    $week_start=false;
    //echo "today".date("w")."<br>";   
    for ($i=-6;$i<37;$i++)
    {
        if ($i<0&&date("w")!=0)
            {
            if (!$week_start)    
                {
                //echo "w".date("w",strtotime($i." day"))."<br>";   
                if (date("w",strtotime($i." day"))==0)
                    {
                    $week_start=true;
                    $html.=cell_html($i,"day_num ignore");    
                    }
                else
                    continue;       
                }
            else
                $html.=cell_html($i,"day_num ignore");   
            }
        if ($i==0)  
            $html.=cell_html($i,"day_num selected");
        if ($i>0 && $i<=29)
            $html.=cell_html($i,"day_num");
        if ($i>29)
        {
            if (date("w",strtotime($i." day"))==6)
            {
                $html.=cell_html($i,"day_num ignore");
                break;     
            }
            else
                $html.=cell_html($i,"day_num ignore");
        }
    }
    return $html;
}
function cell_html($i,$css_name)
{
    $temp_html="";
    $temp_html.='<div class="'.$css_name.'">';
    //<span>3</span><div class="event green">Birthday</div>
    if (date("d",strtotime("+".$i." day"))=="01"){
        $temp_html.=date("m",strtotime("+".$i." day"))." 月 ";
    }
    if  ($i>=0) 
        $temp_html.=date("j",strtotime("+".$i." day"));
    else
        $temp_html.=date("j",strtotime("".$i." day")); 
    $temp_html.="";  
    $temp_html.=display_events($i);      
    $temp_html.="</div>\n";
    //echo "**".$temp_html."**";
    return $temp_html;
}
function display_events($i){
    global $wooden_walkways;
    if ($i<0 || $i>=30)
        return "";
    $temp_html="";
    for ($j=0;$j<count($wooden_walkways[$i]->open_times_start);$j++){
        $temp_html.='<div class="event green">';
        $temp_html.=$wooden_walkways[$i]->open_times_start[$j]."-";
        $temp_html.=$wooden_walkways[$i]->open_times_end[$j];
        $temp_html.="</div>";
    }
    return $temp_html;
}
?>
