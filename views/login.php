<div class="row" style="justify-content: center; align-items: center; min-height: 50vh;">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center">
                <h2>Iniciar Sesión</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?page=auth&action=login">
                    <div class="form-group">
                        <label for="username">Usuario (Nombre)</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Ingrese su nombre registrado" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese su contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" style="width:100%">Ingresar</button>
                </form>
            </div>
            <div class="card-footer text-muted text-center">
                <small>Sistema de Despacho y Logística</small>
            </div>
        </div>
    </div>
</div>
