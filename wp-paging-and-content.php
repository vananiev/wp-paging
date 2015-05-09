<?php
/*
Plugin Name: Auto paging mb & glaving
Plugin URI: http://blog.portal.kharkov.ua/2008/01/24/paging-plugin-vozvrashhaetsya/
Description: Automatic pagination for long posts. Use wp_link_pages() or link_pages() in template.
Author: Yuri 'Bela' Belotitski, Ananiev Vitalii
Version: 1.4
Author URI: http://blog.portal.kharkov.ua/
*/ 
/*  Copyright Yuri 'Bela' Belotitski
    Partial copyright Earl Miles.
*/
 

function paging($posts) {

	$paging_number = 3000; ### Максимальный размер страницы в символах
	
		if(!isset($_GET['page']))
			$page = 1;
		else
			$page = $_GET['page'];
        if (!$posts[1] && (strpos($posts[0]->post_content,'<!--nextpage-->') === false)
                       && (strpos($posts[0]->post_content,'<!--nopage-->') === false)) {
          $body = $posts[0]->post_content;
          $words_count = mb_strlen($body,'UTF-8');
          if ($words_count > $paging_number) {
			while (strlen(trim($body)) && ($i<1000)) {
				$bodypart[$i] = paging_paragraph_split($body, $paging_number);
				$bodycount += mb_strlen($bodypart[$i],'UTF-8');
				$body = mb_substr($posts[0]->post_content, $bodycount, $paging_number, 'UTF-8');
				//Поиск оглавления
				if($page==0 || $page==1)
					{
					$s = mb_strpos($bodypart[$i], "<h",0,'UTF-8');
					while(!($s===false) && $k < 1000){
						$e = mb_strpos($bodypart[$i], "</h", $s, 'UTF-8');
						$level = mb_substr($bodypart[$i], $s+2, 1, 'UTF-8');
						if($level < 10 && $level > 0)
							{
							$content = mb_substr($bodypart[$i], $s+4, $e - ($s+4), 'UTF-8');
							if($level>4) $level = 4;
							$oglablenie .= "<h".($level+2)." id='post-".$posts[0]->ID."' style='line-height:1.1em;padding-left:".($level*20)."px'><a href='".$posts[0]->guid ."&page=".($i+2)."' title='".$content."'>".$content."</a></h".($level+2).">";
							}
						$s = mb_strpos($bodypart[$i], "<h", $s+2, 'UTF-8');
						$k++;
						}
					}
				$i++;
            }
			if($oglablenie != "")
				$posts[0]->post_content = "<h2 style='margin-bottom:20px;'>Огравление:</h2>".$oglablenie;
			else
				$posts[0]->post_content = "<p></p>";
			$posts[0]->post_content .= '<!--nextpage-->'.implode('<!--nextpage-->', $bodypart);
		  }
	}
	return $posts;
}

function paging_paragraph_split($body, $size) {
  
  if (mb_strlen($body,'UTF-8') < $size) {
    return $body;
  }
  
  $teaser = mb_substr($body, 0, $size,'UTF-8');
  $position = 0;
  $breakpoints = array('</p>','<br />','<br>',"\n");
  foreach ($breakpoints as $point) {
    $length_ = mb_strrpos($teaser, $point,'UTF-8');
    if ($length_ > $length) $length = $length_;
  }

  if ($length) {
      $position = $length;
      return ($position == 0) ? $teaser : mb_substr($teaser, 0, $position,'UTF-8');
  }

  $breakpoints = array('. ', '!', '?', ' - ');
  $min_length = mb_strlen($reversed,'UTF-8');
  foreach ($breakpoints as $point) {
    $length_ = mb_strrpos($teaser, $point,'UTF-8');
    if ($length_ > $length) $length = $length_;
  }

  if ($length) {
      $position = $length;
  }
  return ($position == 0) ? $teaser : mb_substr($teaser, 0, $position,'UTF-8');

}

if (!is_admin()) add_filter('the_posts', 'paging', 10000);

function alt_link_pages($left=5, $center=5, $right=5) {
	global $post, $page, $numpages, $multipage;
	$output = '';
if ( $multipage ) {
	if ($numpages <= $left + $center + $right) {
	    for ($i = 1; $i <= $numpages; $i++) {
			$output .= alt_link_pages_i($i);
		}
	} 
	elseif($page < $left + $center) { 
		$lc = $left + $center;
		for($i = 1; $i <= ($lc); $i ++) {
			$output .= alt_link_pages_i($i);
		}
		$output .= "... ";
		for($i = $numpages-$right+1; $i <= $numpages; $i++) {
			$output .= alt_link_pages_i($i);
		}
	}
	elseif(($page >= $left + $center) && ($page < $numpages - $right - $center + 1)) {
		for($i = 1; $i <= $left; $i ++) {
			$output .= alt_link_pages_i($i);
		}
		$output .= "... ";
		$c = floor ( $center / 2 );
		for($i = $page - $c; $i <= $page + $c; $i ++) {
			$output .= alt_link_pages_i($i);
		}
		$output .= "... \n";
		for($i = $numpages-$right+1; $i <= $numpages; $i++) {
			$output .= alt_link_pages_i($i);
		}
	}
	else {
		for($i = 1; $i <= $left; $i ++) {
			$output .= alt_link_pages_i($i);
		}
		$output .= "... ";
		for($i = $numpages - $right - $center; $i <= $numpages; $i ++) {
			$output .= alt_link_pages_i($i);
		}
	}
}
echo $output;
}

function alt_link_pages_i($i) {
	global $page;
	if ($page == $i) {
		return $i.' ';
	}
	elseif ( 1 == $i ) {
		 return '<a href="' . get_permalink() . '">'.$i.'</a> ';
	} 
	else {
		if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
			return '<a href="' . get_permalink() . '&amp;page=' . $i . '">'.$i.'</a> ';
		else
			return '<a href="' . trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged') . '">'.$i.'</a> ';
	}
}

?>