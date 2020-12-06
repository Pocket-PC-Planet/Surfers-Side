<?php
include('head.html');
include('mysqlconnect.php');
error_reporting(E_ALL);
try {
    $pdo = new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
} catch (PDOException $exception) {
    // If there is an error with the connection, stop the script and display the error
    exit('Failed to connect to database!');
}

// Below function will convert datetime to time elapsed string
function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array('y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second');
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// This function will populate the posts and posts replies using a loop
function show_posts($posts, $parent_id = -1)
{
    //if searching

    $html = '';
    if ($parent_id != -1) {
        // If the posts are replies sort them by the "submit_date" column
        array_multisort(array_column($posts, 'submit_date'), SORT_ASC, $posts);
    }

    $resultCount = 0;
	$totalPosts = 0;

    $query = strtolower($_GET['search_query']);
    list($searchTxt, $type, $category) = explode('^', $query);

    if ($searchTxt == "") {
        $searchTxt = " ";
    }


    if ($type == "") {
        $type = " ";
    }


    if ($category == "") {
        $category = " ";
    }


    // Iterate the posts using the foreach loop
    foreach ($posts as $post) {
        if ($searchTxt != " " || $type != " " || $category != " ") {
            if ($post['parent_id'] == $parent_id) {
                if (strpos(strtolower(implode($post)), $searchTxt) && strpos(strtolower(implode($post)), $type) && strpos(strtolower(implode($post)), $category)) {
                    if ($post['cab'] == 1 && $post['direct'] == 1) {
                        $resultCount++;

                        //check if optional variables are not set
                        $screenshot = $post['screenshot'];
                        if ($screenshot == "") {
                            $screenshot = "http://appmanager.ppcplanet.org/images/noscreenshot.png";
                        } else {
                            //remove https from screenshot
                            $screenshot = str_replace("https", "http", $screenshot);
                        }

                        $serial = $post['serial'];
                        if ($serial == "") {
                            $serial = "n/a";
                        }

                        $source = $post['source'];
                        if ($source == "") {
                            $source = "n/a";
                        }


                        //remove https from download URL
                        $downloadURL = $post['url'];
                        $downloadURL = str_replace("https", "http", $downloadURL);
                        $name = nl2br(htmlspecialchars($post['name'], ENT_QUOTES));
                        $name = str_replace(" ", "_", $name);
                        $downloadURL = $downloadURL . '#' . $name;

                        $html .= '
                <div class="post">
                <h2 class="content"><b>' . nl2br(htmlspecialchars($post['name'], ENT_QUOTES)) . '</a></b></h2>
                <h3 style="color: white;" class="name"><b>By ' . htmlspecialchars($post['postauthor'], ENT_QUOTES) . '</b> - <span class="date">' . time_elapsed_string($post['submit_date']) . '</span></h3>
                <img class="image" style="width: 256px; overflow: hidden; object-fit: cover;" src=' . nl2br(htmlspecialchars($screenshot, ENT_QUOTES)) . ' alt="No Screenshot"/>
                <p class="content"><b>Description: </b>' . nl2br(htmlspecialchars($post['content'], ENT_QUOTES)) . '</p>
                <p class="content"><b>Serial: </b>' . nl2br(htmlspecialchars($serial, ENT_QUOTES)) . ' </p>
                <p class="content"><b>Type: </b>' . nl2br(htmlspecialchars($post['type'], ENT_QUOTES)) . ' </p>
                <p class="content"><b>Category: </b>' . nl2br(htmlspecialchars($post['category'], ENT_QUOTES)) . ' </p>
                <h3><a href=' . nl2br(htmlspecialchars($downloadURL, ENT_QUOTES)) . ' target="_blank">Download</a></h3>
				<hr>
                </div>
                ';
                    }
                }
            }

            ob_clean(); //clear previously echoed text
            include('head.html');
            echo (strval($resultCount) . ' result(s) found for "query: ' . $searchTxt . " + type: " . $type . " + category: " . $category . '"'); //display number of results
        } else {
            //if not searching
			
            //add each post to HTML variable
            if ($post['parent_id'] == $parent_id) {
                if ($post['cab'] == 1 && $post['direct'] == 1) {
					$totalPosts++;
                    //check if optional variables are not set
                    $screenshot = $post['screenshot'];
                    if ($screenshot == "") {
                        $screenshot = "http://appmanager.ppcplanet.org/images/noscreenshot.png";
                    } else {
                        //remove https from screenshot URL
                        $screenshot = str_replace("https", "http", $screenshot);
                    }

                    $serial = $post['serial'];
                    if ($serial == "") {
                        $serial = "n/a";
                    }

                    //remove https from download URL
                    $downloadURL = $post['url'];
                    $downloadURL = str_replace("https", "http", $downloadURL);
                    $name = nl2br(htmlspecialchars($post['name'], ENT_QUOTES));
                    $name = str_replace(" ", "_", $name);
                    $downloadURL = $downloadURL . '#' . $name;

                    $html .= '
            <div class="post">
                <h2 class="content"><b>' . nl2br(htmlspecialchars($post['name'], ENT_QUOTES)) . '</a></b></h2>
                <h3 style="color: white;" class="name"><b>By ' . htmlspecialchars($post['postauthor'], ENT_QUOTES) . '</b> - <span class="date">' . time_elapsed_string($post['submit_date']) . '</span></h3>
                <img class="image" style="width: 256px; overflow: hidden; object-fit: cover;" src=' . nl2br(htmlspecialchars($screenshot, ENT_QUOTES)) . ' alt="No Screenshot"/>
                <p class="content"><b>Description: </b>' . nl2br(htmlspecialchars($post['content'], ENT_QUOTES)) . '</p>
                <p class="content"><b>Serial: </b>' . nl2br(htmlspecialchars($serial, ENT_QUOTES)) . ' </p>
                <p class="content"><b>Type: </b>' . nl2br(htmlspecialchars($post['type'], ENT_QUOTES)) . ' </p>
                <p class="content"><b>Category: </b>' . nl2br(htmlspecialchars($post['category'], ENT_QUOTES)) . ' </p>
                <h3><a href=' . nl2br(htmlspecialchars($downloadURL, ENT_QUOTES)) . ' target="_blank">Download</a></h3>
				<hr>
            </div>
            ';
                        
                    ob_clean(); //clear previously echoed text
                    include('head.html');
                    echo (strval($totalPosts) . ' total posts');
                }
            }
        }
    }
	
    return $html;
}

if (isset($_GET['search_query'])) {
    // Get all posts by the Page ID ordered by the submit date
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE page_id = ? ORDER BY submit_date DESC');
    $stmt->execute([1]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get the total number of posts
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total_posts FROM posts WHERE page_id = ?');
    $stmt->execute([1]);
    $posts_info = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    exit('No search query specified!');
}
?>

<div class="post_header">
    <span style="color: white;" class="total"><?= $posts_info['total_posts'] ?> total post(s)</span>
</div>

<?= show_posts($posts) ?>