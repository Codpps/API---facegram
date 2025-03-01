<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Facegram</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container">
        <a class="navbar-brand m-auto" href="index.html">Facegram</a>
    </div>
</nav>

<main class="mt-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between bg-transparent py-3">
                        <h5 class="mb-0">Login</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username"/>
                            </div>

                            <div class="mb-3">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password"/>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Login
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    Don't have account? <a href="register.html">Register</a>
                </div>

            </div>
        </div>
    </div>
</main>

</body>
</html>
