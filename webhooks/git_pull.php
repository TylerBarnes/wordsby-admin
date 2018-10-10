<?php 

require_once(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/wp-load.php');  

if (get_field('how_should_we_populate_the_template_dropdown', 'option') !== 'gatsby_repo') die(); 

$git_repo_url = get_field('gatsby_git_repo', 'options'); 

function should_we_clone_the_repo() {
    global $git_repo_url;

    $is_gatsby_dir_empty = count(glob("../gatsby/*")) === 0;

    if (!$git_repo_url) {
        // echo "<p>Git repo url is not set</p>";
        return false;
    };

    if (!$is_gatsby_dir_empty) {
        // echo '<p>Repo already cloned</p><br>';
        return false;
    }

    if (!`git ls-remote {$git_repo_url}`) {
        // echo '<p>Not a valid repo..</p>';
        return false;
    }

    return 'yes';
}

function try_to_pull_the_repo() {
    if (file_exists('../gatsby')) {
        chdir('../gatsby');
        exec('git pull 2>&1');
    }
}

if (should_we_clone_the_repo() === 'yes') {
    if(!file_exists('../gatsby')) mkdir('../gatsby', 0755);

    if(file_exists('../gatsby/.gitkeep')) unlink("../gatsby/.gitkeep");
    
    echo exec("cd ../gatsby && git clone $git_repo_url .");
} else {
    try_to_pull_the_repo();
}

?>
