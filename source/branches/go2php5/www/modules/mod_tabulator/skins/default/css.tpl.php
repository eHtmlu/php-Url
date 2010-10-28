<?php header('Content-Type: text/css; '); ?>

*
{
	margin:0px;
	padding:0px;
	border-style:none;
	font-family:Verdana;
	font-size:11px;
	color:#000;
	text-decoration:none;
}

a
{
	cursor:pointer;
}

html,body
{
	width:100%;
	height:100%;
	overflow:hidden;
}

body
{
	background-color:#fff;
/*	background-image:url(<?php echo $this->modpath; ?>/test_img/background.jpg);*/
}

.JSON, .hidden
{
	display:none;
}

div#top
{
	position:absolute;
	height:64px;
	width:100%;
	background-color:#960;
	/*background-color:#c06;*/
	background-image:url(<?php echo $this->skinpath; ?>/img/main_bg.png);
	background-repeat:repeat-x;
	/*background-image:url(<?php echo $this->modpath; ?>/test_img/background.jpg);*/
}

a#addtab
{
	position:absolute;
	left:69px;
	top:32px;
	width:32px;
	height:32px;
	overflow:hidden;
	background-image:url(<?php echo $this->skinpath; ?>/img/main_addtab_0.png);
	background-repeat:no-repeat;
}

a#addtab span
{
	display:block;
	margin-left:50px;
}

a#addtab:hover
{
	background-image:url(<?php echo $this->skinpath; ?>/img/main_addtab_1.png);
}

iframe
{
	overflow:auto;
	background-color:#fff;
	position:absolute;
	top:70px;
	left:0px;
	width:100%;
	height:85%; /* the correct value you have to set by javascript */
}

div#tabarea
{
	height:32px;
	margin:0px 0px 0px 101px;
	background-image:url(<?php echo $this->skinpath; ?>/img/main_tabarea_bg.png);
	background-repeat:repeat-x;
	overflow:hidden;
}

h1
{
	position:absolute;
	left:0px;
	top:0px;
	width:64px;
	height:64px;
	overflow:hidden;
}

h1 span
{
	margin-left:100px;
}


div#control_panel
{
	position:absolute;
	right:0px;
	top:0px;
	height:32px;
	background-color:#f9f;
}

form#search
{
	position:absolute;
	top:0px;
	right:85px;
	width:150px;
	height:32px;
	background-image:url(<?php echo $this->skinpath; ?>/img/main_search_bg.png);
	background-repeat:no-repeat;
}

form#search input
{
	margin-left:23px;
	margin-top:8px;
	height:18px;
	width:120px;
	background-color:transparent;
	line-height:20px;
	color:#fff;
}

a#logout
{
	display:block;
	position:absolute;
	top:0px;
	right:5px;
	width:32px;
	height:32px;
	overflow:hidden;
	background-image:url(<?php echo $this->skinpath; ?>/img/main_logout_0.png);
	background-repeat:no-repeat;
}

a#logout span
{
	display:block;
	margin-left:50px;
}

a#logout:hover
{
	background-image:url(<?php echo $this->skinpath; ?>/img/main_logout_1.png);
}

object {
	position:absolute;
	top:4px;
	right:46px;
}

div#shortcut_menu
{
	/*position:absolute;
	top:0px;
	left:101px;*/
	margin-left:101px;
	margin-right:235px;
	overflow:hidden;
	height:32px;
}

div#shortcut_menu a
{
	display:block;
	width:32px;
	height:32px;
	float:left;
	overflow:hidden;
}

div#shortcut_menu a span
{
	display:block;
	height:32px;
	padding-left:50px;
	margin:7px auto auto 6px;
}

div#shortcut_menu a:hover
{
	background-image:url(<?php echo $this->skinpath; ?>/img/main_shortcut_bg.png);
}

/* digital */
div#clock div
{
	/*display:inline;*/
}

/* analog */

div#clock
{
	overflow:hidden;
	width:25px;
	height:25px;
	position:absolute;
	top:4px;
	right:46px;
	background-image:url(<?php echo $this->skinpath; ?>/img/clock_bg.png);
	background-repeat:no-repeat;
}

div#clock span
{
	display:block;
	position:absolute;
	width:75px;
	height:1525px;
	overflow:hidden;
	background-image:url(<?php echo $this->skinpath; ?>/img/clock.png);
}

div#clock span.colon
{
	display:block;
	margin-left:100px;
}

div#clock span.digit
{
	display:block;
	margin-left:100px;
}

span#clock_h
{
	left:0px;
}

span#clock_m
{
	left:-25px;
}

span#clock_s
{
	left:-50px;
}

div#tabarea ol
{
	list-style-type:none;
	position:relative;
/*	margin-left:6px;*/
}

div#tabarea ol li
{
	position:relative;
	top:6px;
	opacity:0.5;
	filter:alpha(opacity=50);
}

div#tabarea ol li:hover
{
	opacity:0.65;
	filter:alpha(opacity=60);
}

div#tabarea ol li.active
{
	opacity:1;
	z-index:100;
}

div#tabarea ol li
{
	float:left;
	height:26px;
	margin-right:24px;
}

div#tabarea ol li a.item
{
	background-image:url(<?php echo $this->skinpath; ?>/img/tab_lm.png);
	background-repeat:no-repeat;
/*	background-image:url(<?php echo $this->skinpath; ?>/img/tab_r.png);
	background-repeat:no-repeat;
	background-position:right top;
*/
	display:block;
	float:left;
	height:26px;
	min-width:150px;
	/*position:relative;*/
	/*right:-32px;*/
}

div#tabarea ol li a.item span.s1
{
	background-image:url(<?php echo $this->skinpath; ?>/img/tab_r_cut.png);
	background-repeat:no-repeat;
	display:block;
	height:26px;
	width:32px;
	position:absolute;
	right:-32px;
	top:0px;
}

div#tabarea ol li.active a.item span.s1
{
	background-image:url(<?php echo $this->skinpath; ?>/img/tab_r.png);
}

div#tabarea ol li.last a.item span.s1
{
	background-image:url(<?php echo $this->skinpath; ?>/img/tab_r.png);
}

/*
div#tabarea ol li a.item span.itemcontent
{
	display:block;
	height:26px;
	position:relative;
	right:-51px;
}

*/
div#tabarea ol li a.item span.itemcontent
{
	margin-top:3px;
	margin-left:6px;
	display:block;
	padding-left:23px;
	padding-top:3px;
	padding-bottom:10px;
	padding-right:10px;
}

div#tabarea ol li a.item span.itemcontent span
{
	display:block;
	position:absolute;
	left:6px;
	top:3px;
	width:20px;
	height:20px;
}



div#tabarea ol li a.close
{
	position:absolute;
	right:-13px;
	top:4px;
	overflow:hidden;
	display:block;
	width:13px;
	height:13px;
	background-image:url(<?php echo $this->skinpath; ?>/img/tab_close_0.png);
	background-repeat:no-repeat;
}

div#tabarea ol li a.close:hover
{
	background-image:url(<?php echo $this->skinpath; ?>/img/tab_close_1.png);
}

div#tabarea ol li a.close span
{
	margin-left:20px;
}

/*div#tabarea ol li a.close
{
	display:none;
}
*/

div#tabcontrols
{
	position:absolute;
	top:32px;
	right:0;
	width:200px;
	height:32px;
	background-image:url(<?php echo $this->skinpath; ?>/img/main_tabarea_bg.png);
	background-repeat:repeat-x;

/*	background:#f00;*/
}

.scrollbar
{
	top:6px;
	left:10px;
	position:relative;
	height:20px;
	width:160px;
	background-image:url(<?php echo $this->skinpath; ?>/img/tabcontrols_slider_background.png);
	background-repeat:repeat-x;
}

.scrollbar div
{
	position:absolute;
	height:20px;
	background-image:url(<?php echo $this->skinpath; ?>/img/tabcontrols_slider.png);
}

.scrollbar span
{
	display:block;
	float:right;
	width:10px;
	height:20px;
	background-image:url(<?php echo $this->skinpath; ?>/img/tabcontrols_slider.png);
	background-position:10px 0;
}

.scrollbar div.scroll, .scrollbar div.hover
{
	background-position:0 20px;
}

.scrollbar div.scroll span, .scrollbar div.hover span
{
	background-position:10px 20px;
}

div#tabcontrols .button
{
	position:absolute;
	right:0px;
	top:6px;
	width:20px;
	height:20px;
}
