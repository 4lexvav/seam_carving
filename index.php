<?php
set_time_limit(0);
ini_set('max_execution_time', 0);

if (isset($_FILES['image']['tmp_name'], $_POST['size'])) {
    require_once './vendor/autoload.php';

    // save image
    $imageName = addslashes($_FILES['image']['name']);
    $targetDir = 'web/';
    $targetFile = $targetDir . basename($imageName);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check file size
    if ($_FILES['image']['size'] > 500000) {
        echo 'Sorry, your file is too large.';
        exit();
    }

    // Allow certain file formats
    if($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg' && $imageFileType != 'gif' ) {
        echo 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
        exit();
    }

    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

    // prepare size
    $size = explode('x', strtolower(htmlspecialchars(strip_tags($_POST['size']))));
    $x = $size[0] ?? 0;
    $y = $size[1] ?? 0;

    // prepare file names
    $targetFile = str_replace('.'.$imageFileType, '', $targetFile);
    $baseImgFile = $targetFile . '.' . $imageFileType;
    $dualImgFile = $targetFile . '_dual.' . $imageFileType;
    $baseImgWithSeamsFile = $targetFile . '_seams.' . $imageFileType;
    $resizedImgFile = $targetFile . '_resized.' . $imageFileType;

    // resize image
    $picture = new Picture($baseImgFile);
    $seamCarver = new SeamCarver($picture);

    $seamCarver->outputDualGradientPicture($dualImgFile);

    # collect and remove seams
    $hSeams = [];
    $vSeams = [];
    for ($i = 0; $i < $x; $i++) {
        $vSeams[] = $seamCarver->findVerticalSeam();
        $seamCarver->removeVerticalSeam($vSeams[$i]);
    }

    for ($i = 0; $i < $y; $i++) {
        $hSeams[] = $seamCarver->findHorizontalSeam();
        $seamCarver->removeHorizontalSeam($hSeams[$i]);
    }

    // output images with removed seams
    $picture->output($resizedImgFile);
    $picture->outputWithSeams($hSeams, $vSeams, $baseImgWithSeamsFile);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seam Carving</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>

<main role="main">

    <section class="jumbotron text-center">
        <div class="container">
            <h1 class="jumbotron-heading">Seam Carving</h1>
            <p class="lead text-muted">Content-aware image resizing techinque.</p>
        </div>
    </section>

    <div class="container mb-5">
        <div class="row justify-content-md-center">
            <div class="col-md-4">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" name="image" class="form-control-file" id="image" aria-describedby="imageHelp" placeholder="Upload image">
                        <small id="imageHelp" class="form-text text-muted">Please upload image you want to resize.</small>
                    </div>
                    <div class="form-group">
                        <label for="size">Reduce by number of pixels</label>
                        <input type="text" name="size" class="form-control" id="size" placeholder="WxH">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <?php if (isset($baseImgFile)): ?>
        <div class="album py-5 bg-light">
            <div class="container">

                <div class="row mb-5">
                    <img class="img-fluid mx-auto" alt="Responsive image" src="<?= $baseImgFile ?>">
                </div>
                <div class="row mb-5">
                    <img class="img-fluid mx-auto" alt="Responsive image" src="<?= $dualImgFile ?>">
                </div>
                <div class="row mb-5">
                    <img class="img-fluid mx-auto" alt="Responsive image" src="<?= $resizedImgFile ?>">
                </div>
                <div class="row mb-5">
                    <img class="img-fluid mx-auto" alt="Responsive image" src="<?= $baseImgWithSeamsFile ?>">
                </div>
            </div>
        </div>
    <?php endif ?>

</main>

<footer class="text-muted">
    <div class="container">
        <p class="float-right">
            <a href="#">Back to top</a>
        </p>
    </div>
</footer>

<script type="text/javascript">
    vSeams = <?= json_encode($vSeams) ?>;
    hSeams = <?= json_encode($hSeams) ?>;
</script>
</html>
