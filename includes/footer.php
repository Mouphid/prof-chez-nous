    </main>

    <footer class="bg-gray-900 text-gray-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <img src="../img/logo.jpg" alt="Joie Enseignante" class="h-10 w-auto mb-3 bg-white p-1 rounded">
                <p class="text-sm text-gray-400">Plateforme pédagogique pour enseignants et étudiants. Partagez vos connaissances et ressources éducatives.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Liens rapides</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="../public/index.php" class="hover:text-white transition"><i class="ph ph-house mr-2"></i> Accueil</a></li>
                    <li><a href="../public/about.php" class="hover:text-white transition"><i class="ph ph-info mr-2"></i> À propos</a></li>
                    <li><a href="../public/biography.php" class="hover:text-white transition"><i class="ph ph-user-circle mr-2"></i> Biographie</a></li>
                    <li><a href="../public/contact.php" class="hover:text-white transition"><i class="ph ph-envelope mr-2"></i> Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Informations légales</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="../public/mentions-legales.php" class="hover:text-white transition"><i class="ph ph-file-text mr-2"></i> Mentions légales</a></li>
                    <li><a href="../public/conditions-utilisation.php" class="hover:text-white transition"><i class="ph ph-scroll mr-2"></i> Conditions d'utilisation</a></li>
                    <li><a href="../public/confidentialite.php" class="hover:text-white transition"><i class="ph ph-shield-check mr-2"></i> Politique de confidentialité</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Suivez-nous</h4>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 bg-gray-700 hover:bg-indigo-600 rounded-lg flex items-center justify-center transition" title="Facebook"><i class="ph ph-facebook-logo"></i></a>
                    <a href="#" class="w-9 h-9 bg-gray-700 hover:bg-indigo-600 rounded-lg flex items-center justify-center transition" title="Twitter"><i class="ph ph-x-logo"></i></a>
                    <a href="#" class="w-9 h-9 bg-gray-700 hover:bg-indigo-600 rounded-lg flex items-center justify-center transition" title="YouTube"><i class="ph ph-youtube-logo"></i></a>
                    <a href="#" class="w-9 h-9 bg-gray-700 hover:bg-indigo-600 rounded-lg flex items-center justify-center transition" title="LinkedIn"><i class="ph ph-linkedin-logo"></i></a>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-700 py-6">
            <p class="text-center text-sm text-gray-500">
                &copy; <?= date('Y') ?> Joie Enseignante. Tous droits réservés.
            </p>
        </div>
    </footer>
<script>function toggleMobileMenu(b){var m=document.getElementById('mobileNav');if(m){m.classList.toggle('hidden');b.setAttribute('aria-expanded',!m.classList.contains('hidden'));}}</script>
</body>
</html>
