<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Biographie - Joie Enseignante";

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center py-16" style="background-image: url('<?= BASE_URL ?>img/bg/banner-biography.jpg');">
    <div class="absolute inset-0 bg-white/75"></div>
    <div class="relative z-10 text-center">
        <h1 class="text-3xl font-bold text-gray-900"><i class="ph ph-user-circle text-primary"></i> Biographie</h1>
        <p class="text-gray-500 mt-2">Découvrez le parcours du fondateur</p>
    </div>
</div>

<div id="main-content" class="max-w-5xl mx-auto px-4 py-12">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-12">
        <div class="flex flex-col lg:flex-row gap-10">
            <div class="lg:w-72 flex-shrink-0">
                <img src="../img/P.jpg" alt="Sylvestre Djouamon" class="w-full rounded-2xl shadow-md">
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Sylvestre Djouamon</h2>
                <p class="text-primary font-medium mb-6">Maître-Assistant au département des Lettres Modernes — FLLAC</p>

                <p class="text-gray-600 leading-relaxed mb-6">
                    Sylvestre Djouamon est Maître-Assistant au département des Lettres Modernes à la Faculté des Lettres, 
                    Langues, Arts et Communication (FLLAC). Sa thèse, intitulée <em>« Le fonctionnement du pessimisme dans 
                    les chansons traditionnelles modernes fons et maxis du Bénin »</em>, explore les dynamiques du discours 
                    littéraire dans la musique du Sud-Bénin.
                </p>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-3"><i class="ph ph-book-open text-primary"></i> Parcours Académique</h3>
                        <p class="text-gray-600 leading-relaxed mb-2">Sylvestre Djouamon possède une formation pluridisciplinaire avec plusieurs diplômes :</p>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex gap-3"><i class="ph ph-graduation-cap text-primary mt-1 flex-shrink-0"></i> Doctorat en Lettres Modernes</li>
                            <li class="flex gap-3"><i class="ph ph-graduation-cap text-primary mt-1 flex-shrink-0"></i> Maîtrise en Droit</li>
                            <li class="flex gap-3"><i class="ph ph-graduation-cap text-primary mt-1 flex-shrink-0"></i> Expert-Évaluateur en Politiques Publiques</li>
                            <li class="flex gap-3"><i class="ph ph-graduation-cap text-primary mt-1 flex-shrink-0"></i> Master en Communication Commerciale et Stratégies</li>
                            <li class="flex gap-3"><i class="ph ph-graduation-cap text-primary mt-1 flex-shrink-0"></i> Certificat en Journalisme et Communication</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-3"><i class="ph ph-buildings text-primary"></i> Carrière Universitaire</h3>
                        <p class="text-gray-600 leading-relaxed mb-2">
                            Actuellement, il est chef adjoint du département des Lettres Modernes à l'Université d'Abomey-Calavi, 
                            où il enseigne plusieurs cours :
                        </p>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex gap-3"><i class="ph ph-chalkboard text-primary mt-1 flex-shrink-0"></i> Phénoménologie de la littérature orale</li>
                            <li class="flex gap-3"><i class="ph ph-chalkboard text-primary mt-1 flex-shrink-0"></i> La chanson traditionnelle béninoise et ses innovations</li>
                            <li class="flex gap-3"><i class="ph ph-chalkboard text-primary mt-1 flex-shrink-0"></i> Le conte africain</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-3"><i class="ph ph-briefcase text-primary"></i> Consultations et Expertise</h3>
                        <p class="text-gray-600 leading-relaxed mb-2">En parallèle, il intervient comme consultant pour diverses institutions :</p>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex gap-3"><i class="ph ph-handshake text-primary mt-1 flex-shrink-0"></i> Consultant AUF au Haut Conseil de l'Éducation en République du Congo</li>
                            <li class="flex gap-3"><i class="ph ph-handshake text-primary mt-1 flex-shrink-0"></i> Rapporteur général de Conférence régionale sur l'évaluation d'impact en Afrique francophone</li>
                            <li class="flex gap-3"><i class="ph ph-handshake text-primary mt-1 flex-shrink-0"></i> Consultant juridique pour l'évaluation Plan Foncier Rural (Banque mondiale)</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-3"><i class="ph ph-newspaper text-primary"></i> Engagement Journalistique</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Sylvestre Djouamon est également journaliste et directeur de publication du magazine 
                            <strong>Afrique Identité</strong>. Il est membre actif de l'Association des journalistes 
                            scientifiques du Bénin et coordonnateur du projet École Avenir, visant à améliorer le 
                            système éducatif africain.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-3"><i class="ph ph-magnifying-glass text-primary"></i> Axes de Recherche</h3>
                        <p class="text-gray-600 leading-relaxed mb-2">Ses recherches portent principalement sur :</p>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex gap-3"><i class="ph ph-dots-three text-primary mt-1 flex-shrink-0"></i> Littérature orale et transmission des savoirs en Afrique</li>
                            <li class="flex gap-3"><i class="ph ph-dots-three text-primary mt-1 flex-shrink-0"></i> Analyse du discours et représentations sociales dans les chansons béninoises</li>
                            <li class="flex gap-3"><i class="ph ph-dots-three text-primary mt-1 flex-shrink-0"></i> Politiques publiques et impact sur les mentalités collectives</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
