<h2>General Health Information</h2>

<?php
// If you're storing resources in a database, fetch them here
// ... (Database connection and query to fetch articles and videos)

// For now, let's assume you have some static resources
$articles = [
    [
        'title' => 'Dengue Fever Prevention in Sri Lanka',
        'content' => 'Lorem ipsum dolor sit amet...', // Replace with actual article content
        'link' => 'https://www.example.com/dengue-prevention' // Link to the full article (if available)
    ],
    // Add more articles here
];

$videos = [
    [
        'title' => 'Healthy Eating Habits for Sri Lankans',
        'embed_code' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/your-video-id" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>' 
    ],
    // Add more videos here
];
?>

<h3>Articles</h3>
<ul>
    <?php foreach ($articles as $article) { ?>
        <li>
            <h4><?php echo $article['title']; ?></h4>
            <p><?php echo substr($article['content'], 0, 100); ?>...</p> 
            <?php if (isset($article['link'])) { ?>
                <a href="<?php echo $article['link']; ?>" target="_blank">Read More</a>
            <?php } ?>
        </li>
    <?php } ?>
</ul>

<h3>Videos</h3>
<?php foreach ($videos as $video) { ?>
    <h4><?php echo $video['title']; ?></h4>
    <?php echo $video['embed_code']; ?> 
<?php } ?>