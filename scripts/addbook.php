<?php
	if($_COOKIE['uid']){
		$today = date("j.m.y");
		$booksFile = file_get_contents("../database/books.json");
		$booksArray = json_decode($booksFile);
		$userbookexist = false;
		if($_GET['delete']){
			for($a = 0; $a<count($booksArray->books[$_GET['bookid']]->readers); $a++){
				if($booksArray->books[$_GET['bookid']]->readers[$a]->uid == $_COOKIE['uid']){
					array_splice($booksArray->books[$_GET['bookid']]->readers, $a, 1);
					
					$newrating;
					for($i = 0; $i<count($booksArray->books[$_GET['bookid']]->readers); $i++){
						$newrating  = $newrating + $booksArray->books[$_GET['bookid']]->readers[$i]->rating;
					}
					$booksArray->books[$_GET['bookid']]->averagerating = $newrating / $i;

					fwrite(fopen('../database/books.json', 'w'), json_encode($booksArray, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE));

					header('Location: ' . $_SERVER['HTTP_REFERER']);
				}
			}
		}
		
		if($_POST['bookid']){
			for($a = 0; $a<count($booksArray->books[$_POST['bookid']]->readers); $a++){
				if($booksArray->books[$_POST['bookid']]->readers[$a]->uid == $_COOKIE['uid']){
					$userbookexist = true;
				}
			}
			if(!$userbookexist){
				if($_POST['comment']){
					array_push($booksArray->books[$_POST['bookid']]->readers,  array('uid' => $_COOKIE['uid'], 'date' => $today, 'rating' => $_POST['rating'], 'comment' => $_POST['comment'], 'commentrating' => 0));
				}else{
					array_push($booksArray->books[$_POST['bookid']]->readers,  array('uid' => $_COOKIE['uid'], 'date' => $today, 'rating' => $_POST['rating']));
				}
				if(in_array($_COOKIE['uid'], $booksArray->books[$_POST['bookid']]->toread)){
					for($i = 0; $i<count($booksArray->books[$_POST['bookid']]->toread); $i++){
						if($booksArray->books[$_POST['bookid']]->toread[$i] == $_COOKIE['uid']){
							unset($booksArray->books[$_POST['bookid']]->toread[$i]);
						}
					}
				}
				$added = true;
				$newrating;
				for($i = 0; $i<count($booksArray->books[$_POST['bookid']]->readers); $i++){
					$newrating  = $newrating + $booksArray->books[$_POST['bookid']]->readers[$i]->rating;
				}
				$booksArray->books[$_POST['bookid']]->averagerating = $newrating / $i;
			}
		}else{
			if($_POST['title'] && $_POST['author'] && $_POST['rating']){
				(!$_POST['cover']) ? $cover = 'covers/no-book.jpg' : $cover = $_POST['cover'];
				if($_POST['comment']){
					$bookobj = array('id' => count($booksArray->books), 'title' => $_POST['title'],'author' => $_POST['author'],'cover' => $cover,'readers' => array(array('uid' => $_COOKIE['uid'], 'date' => $today, 'rating' => $_POST['rating'], 'comment' => $_POST['comment'],'commentrating' => 0)),'toread' => array(), 'averagerating' => $_POST['rating']);
				}else{
					$bookobj = array('id' => count($booksArray->books), 'title' => $_POST['title'],'author' => $_POST['author'],'cover' => $cover,'readers' => array(array('uid' => $_COOKIE['uid'], 'date' => $today, 'rating' => $_POST['rating'])),'toread' => array(), 'averagerating' => $_POST['rating']);
				}
				array_push($booksArray->books, $bookobj);
				$added = true;
			}
			
		}
		if($added){

			fwrite(fopen('../database/books.json', 'w'), json_encode($booksArray, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE));

			$statsFile = file_get_contents("../database/stats.json");
			$stastArray = json_decode($statsFile);
			$stastArray->books = count($booksArray->books);
			fwrite(fopen('../database/stats.json', 'w'), json_encode($stastArray, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE));
		
		}
		
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
?>
