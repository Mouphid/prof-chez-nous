<?php
$page_title = "Installation - Joie Enseignante";
include 'includes/header.php';
?>

<style>
.install-container { max-width: 600px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.step { margin-bottom: 25px; padding: 20px; border-radius: 8px; background: #f8f9fa; }
.step h3 { margin-bottom: 15px; }
.code-block { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; font-family: monospace; font-size: 0.9rem; }
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }
</style>

<div class="install-container">
    <h1><i class="fas fa-cog"></i> Installation de Joie Enseignante</h1>
    
    <div class="step">
        <h3>1. Créer la base de données</h3>
        <p>Allez dans <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin) et créez une nouvelle base appelée <code>joie_enseignante</code> avec l'interclassement <code>utf8mb4_unicode_ci</code>.</p>
    </div>
    
    <div class="step">
        <h3>2. Importer le schéma SQL</h3>
        <p>Allez dans l'onglet <strong>Importer</strong> et téléversez le fichier <code>sql/joie_enseignante.sql</code>.</p>
    </div>
    
    <div class="step">
        <h3>3. Configurer la base (config/config.php)</h3>
        <p>Vérifiez les informations de connexion dans <code>config/config.php</code>:</p>
        <div class="code-block">
$host = "localhost";<br>
$db_name = "joie_enseignante";<br>
$username = "root"; // ou votre utilisateur MySQL<br>
$password = ""; // ou votre mot de passe MySQL
        </div>
    </div>
    
    <div class="step">
        <h3>4. Comptes par défaut</h3>
        <p>Après l'importation du SQL, vous pouvez vous connecter avec:</p>
        <div class="code-block">
Email: admin@joieenseignante.com<br>
Mot de passe: admin123
        </div>
        <p class="mt-2"><strong>Important:</strong> Changez le mot de passe après la première connexion !</p>
    </div>
    
    <div class="step">
        <h3>5. Créer les dossiers</h3>
        <p>Créez ces dossiers si ils n'existent pas:</p>
        <div class="code-block">
uploads/images/<br>
uploads/docs/<br>
uploads/pdf/<br>
uploads/video/<br>
uploads/audio/<br>
logs/
        </div>
    </div>
    
    <p class="text-center">
        <a href="public/index.php" class="btn btn-primary">Aller au site</a>
        <a href="admin/login.php" class="btn btn-secondary">Connexion Admin</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>