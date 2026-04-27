<?php
session_start();

// Koneksi ke database MariaDB
include './assets/func.php';
$air = new klas_air;
$koneksi = $air->koneksi(); // Sudah ada error handling di dalam koneksi()

// Proses login jika form disubmit
$alert = '';
if (isset($_POST['tombol'])) {
    $username = $_POST['user'];
    $password = $_POST['password'];

    // Gunakan prepared statement untuk keamanan (hindari SQL injection)
    $stmt = mysqli_prepare($koneksi, "SELECT username, password FROM login WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $dc = mysqli_fetch_row($result);

    if (!empty($dc[0])) {
        // Username ditemukan, cek password
        if (password_verify($password, $dc[1])) {
            $_SESSION['user'] = $username;
            $_SESSION['pass'] = $password;
            echo "<script>window.location.replace('./login/index.php')</script>";
            exit;
        } else {
            $alert = '<div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <strong>Login!</strong> Password salah.
                      </div>';
        }
    } else {
        $alert = '<div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <strong>Username!</strong> tidak ditemukan.
                  </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - fla dan ros</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
        <style>
        body.bg-primary {
            background: linear-gradient(135deg, #ff9ecf, #ffc1e3, #ffd6ec);
            font-family: 'DM Sans', sans-serif;
        }

        .card {
            border-radius: 25px;
            background: #fff;
            padding: 20px;
        }

        .card-header {
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        .card-header h3 {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #000;
        }

        .card-header h3 span {
            color: #e84393;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: #e84393;
            box-shadow: 0 0 0 0.2rem rgba(232,67,147,0.2);
        }

        .form-check-input:checked {
            background-color: #e84393;
            border-color: #e84393;
        }

        .btn-primary {
            background: #f1f1f1;
            color: #000;
            border-radius: 12px;
            border: none;
            padding: 8px 22px;
            font-weight: bold;
            box-shadow: 0 5px 12px rgba(0,0,0,0.15);
        }

        .btn-primary:hover {
            background: #e84393;
            color: #fff;
        }

        a {
            color: #e84393;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .card-footer {
            background: transparent;
            border-top: 1px solid #eee;
        }

        .link-profil-footer {
            border: 1px solid #e84393;
            color: #e84393;
            border-radius: 20px;
            padding: 6px 15px;
        }

        .link-profil-footer:hover {
            background: #e84393;
            color: #fff;
        }
        </style>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Login</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php echo $alert; ?>
                                        <form method="post" class="needs-validation">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="inputLogin" type="text" placeholder="Username" name="user" required/>
                                                <label for="inputLogin">Username</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="inputPassword" type="password" placeholder="Password" name="password" required/>
                                                <label for="inputPassword">Password</label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" id="inputRememberPassword" type="checkbox" value="" />
                                                <label class="form-check-label" for="inputRememberPassword">Remember Password</label>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="password.html">Forgot Password?</a>
                                                <input type="submit" name="tombol" value="Login" class="btn btn-primary">
                                            </div>
                                        </form>
                                    </div>

                                    <div class="card-footer text-center py-3">
                                        <div class="small mb-2">
                                            <a href="register.html">Need an account? Sign up!</a>
                                        </div>
                                        <a href="profil.php" class="link-profil-footer">
                                            <i class="fa fa-user-circle"></i>
                                            Kenalan sama Engineer yuk!
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>

            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Flavia &amp; Rosita</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>