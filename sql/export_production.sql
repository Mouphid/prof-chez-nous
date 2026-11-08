-- ============================================================
-- Export de production — Joie Enseignante
-- Généré le 07/11/2026 à 09:10
-- Base : joieenseignante (MySQL 8 / utf8mb4)
--
-- Contenu : structure + données (articles, catégories, documents,
-- cours et chapitres, réglages, compte administrateur).
-- Les tables de ventes, newsletter, journal admin et journal de
-- récupération sont vides.
--
-- IMPORTATION :
--   mysql -u UTILISATEUR -p NOM_DE_LA_BASE < sql/export_production.sql
--
-- ⚠ Changer le mot de passe administrateur juste après la mise en ligne.
-- ============================================================



/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_activity_log` (
  `id_log` int unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `user_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` int DEFAULT NULL,
  `details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_journal_date` (`created_at`),
  KEY `idx_journal_user` (`id_user`),
  KEY `idx_journal_target` (`target_type`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admin_activity_log` WRITE;
/*!40000 ALTER TABLE `admin_activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_activity_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `article_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_reads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_post` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_read` (`id_user`,`id_post`),
  KEY `id_user` (`id_user`),
  KEY `id_post` (`id_post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `article_reads` WRITE;
/*!40000 ALTER TABLE `article_reads` DISABLE KEYS */;
/*!40000 ALTER TABLE `article_reads` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id_category` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#007BFF',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_category`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Cours','cours','Documents de cours','#28a745','2026-09-23 13:35:13');
INSERT INTO `categories` VALUES (2,'Exercices','exercices','Exercices et travaux dirigés','#dc3545','2026-09-23 13:35:13');
INSERT INTO `categories` VALUES (3,'Examens','examens','Sujets d\'examens','#ffc107','2026-09-23 13:35:13');
INSERT INTO `categories` VALUES (4,'Ressources','ressources','Ressources pédagogiques','#17a2b8','2026-09-23 13:35:13');
INSERT INTO `categories` VALUES (5,'Actualités','actualites','Actualités du site','#6f42c1','2026-09-23 13:35:13');
INSERT INTO `categories` VALUES (6,'Publications','publications','','#007BFF','2026-09-23 14:35:13');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id_comment` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `token_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_post` int NOT NULL,
  `author_email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','visible','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `parent_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_comment`),
  KEY `id_post` (`id_post`),
  KEY `status` (`status`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `posts` (`id_post`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `course_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_items` (
  `id_item` int unsigned NOT NULL AUTO_INCREMENT,
  `id_course` int unsigned NOT NULL,
  `title` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_free_preview` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_item`),
  KEY `idx_course` (`id_course`),
  CONSTRAINT `fk_item_course` FOREIGN KEY (`id_course`) REFERENCES `courses` (`id_course`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `course_items` WRITE;
/*!40000 ALTER TABLE `course_items` DISABLE KEYS */;
INSERT INTO `course_items` VALUES (1,1,'Qu\'est-ce que la littérature ?','Définition, fonctions et approches de la littérature : un art du langage qui interroge l\'homme et la société.','## Une définition ouverte\n\nLa littérature est l\'ensemble des œuvres écrites ou orales qui utilisent le langage comme matériau artistique. Elle ne se réduit pas à une liste de livres : c\'est une manière de dire le monde, de raconter des histoires et de faire réfléchir.\n\n## Trois approches complémentaires\n\n- **L\'approche formelle** étudie la langue, le style, la structure et les figures.\n- **L\'approche historique** replace l\'œuvre dans son époque, ses courants et ses influences.\n- **L\'approche sociologique** interroge le rapport entre le texte, la société et le pouvoir.\n\n## À quoi sert la littérature ?\n\nElle sert d\'abord à **comprendre les autres** : en lisant, nous entrons dans des vies qui ne sont pas les nôtres. Elle sert aussi à **penser** : les grandes œuvres posent des questions morales, politiques et existentielles. Enfin, elle sert à **transmettre** : chaque génération reçoit un héritage et le transforme.\n\n## Ce qu\'il faut retenir\n\n**La littérature n\'est pas seulement un contenu à mémoriser.** C\'est une pratique de lecture, d\'interprétation et d\'écriture. Pour analyser un texte, il faut observer ce qu\'il dit, comment il le dit et à qui il s\'adresse.',NULL,NULL,0.00,0,1,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (2,1,'Les genres littéraires','Panorama des grands genres littéraires : roman, poésie, théâtre et essai, et de leurs codes.','## Le roman\n\nLe roman raconte une histoire, réelle ou inventée. Il repose sur des **personnages**, un **cadre**, une **intrigue** et un **narrateur**. Il peut être historique, policier, autobiographique, réaliste ou fantastique.\n\n## La poésie\n\nLa poésie travaille le rythme, les sonorités et les images. Elle se reconnaît souvent à ses vers, à ses strophes et à ses jeux de sonorités, mais elle peut aussi être écrite en prose.\n\n## Le théâtre\n\nLe théâtre est un texte destiné à être joué. Il combine le **dialogue**, les **didascalies** (indications de jeu) et une **action dramatique**. La tragédie, la comédie et le drame en sont les formes principales.\n\n## L\'essai\n\nL\'essai expose une réflexion personnelle sur un sujet. Il argumente, critique, propose. Il se distingue du traité par sa liberté de ton et par la présence de l\'auteur.\n\n## Repères pratiques\n\n- Identifier le genre dès la première lecture.\n- Repérer les codes propres au genre (vers, didascalies, chapitres, thèse).\n- Comparer deux genres pour mieux comprendre leurs frontières.',NULL,NULL,0.00,0,2,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (3,1,'Figures de style','Reconnaître et interpréter les principales figures de style dans un texte littéraire.','## Qu\'est-ce qu\'une figure de style ?\n\nUne figure de style est un procédé qui s\'écarte de l\'usage ordinaire du langage pour produire un effet. Elle ne décore pas seulement le texte : elle **transforme le sens** et guide la lecture.\n\n## Les figures d\'analogie\n\n- **La comparaison** rapproche deux éléments avec un outil : « ses yeux sont bleus comme la mer ».\n- **La métaphore** rapproche sans outil : « ses yeux sont une mer ».\n- **La personnification** donne des traits humains à une chose : « la forêt murmure ».\n\n## Les figures d\'insistance\n\n- **L\'anaphore** répète un mot en début de phrase ou de vers.\n- **L\'hyperbole** exagère volontairement.\n- **La gradation** ordonne les mots selon une intensité croissante.\n\n## Les figures d\'opposition\n\n- **L\'antithèse** oppose deux idées.\n- **L\'oxymore** réunit deux termes contradictoires : « une douce violence ».\n- **Le chiasme** croise les éléments d\'une structure.\n\n## Méthode d\'analyse\n\nNe vous contentez pas de nommer la figure. Expliquez **ce qu\'elle évoque**, **pourquoi elle est employée ici** et **quel effet elle produit sur le lecteur**.',NULL,NULL,0.00,0,3,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (4,1,'Courants littéraires','Des classiques aux modernes : comprendre les grands mouvements qui traversent l\'histoire littéraire.','## Le classicisme\n\nAu XVIIe siècle, le classicisme recherche l\'ordre, la clarté et la mesure. Il s\'appuie sur la raison et sur des règles précises, notamment au théâtre.\n\n## Le romantisme\n\nAu XIXe siècle, le romantisme donne la première place au **sentiment**, à l\'imagination et à la nature. Il valorise l\'individu, la liberté et l\'expression du moi.\n\n## Le réalisme et le naturalisme\n\nLe réalisme veut représenter la société telle qu\'elle est. Le naturalisme pousse cette démarche plus loin en cherchant des explications scientifiques aux comportements humains.\n\n## Le symbolisme et le surréalisme\n\nLe symbolisme cherche à suggérer plutôt qu\'à décrire. Le surréalisme explore l\'inconscient, le rêve et les associations libres.\n\n## La négritude et les littératures africaines\n\nLa négritude affirme la dignité et la richesse des cultures noires. Elle ouvre la voie à une littérature africaine moderne, qui interroge la colonisation, l\'identité et la mémoire.\n\n## Ce qu\'il faut retenir\n\nUn courant littéraire n\'est pas une étiquette figée. Il aide à situer une œuvre, à comprendre ses choix et à dialoguer avec d\'autres textes.',NULL,NULL,0.00,0,4,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (5,2,'Genres de l\'oralité','Contes, épopées, mythes et proverbes : les formes vivantes de la parole africaine.','## Le conte\n\nLe conte est un récit court, souvent merveilleux, transmis oralement. Il met en scène des personnages types et se termine souvent par une morale. Il peut être divertissant, éducatif ou initiatique.\n\n## L\'épopée\n\nL\'épopée raconte les exploits d\'un héros et la destinée d\'un peuple. Elle mêle histoire, légende et surnaturel. En Afrique de l\'Ouest, les épopées sont encore aujourd\'hui portées par des récits et des performances.\n\n## Le mythe et la légende\n\nLe mythe explique les origines du monde, des êtres et des institutions. La légende raconte un événement présenté comme vrai, souvent autour d\'un lieu ou d\'un personnage.\n\n## Le proverbe et la devinette\n\nLe proverbe concentre une sagesse en peu de mots. La devinette propose une énigme à résoudre : elle éduque l\'esprit et renforce la complicité entre les participants.\n\n## Pourquoi ces formes comptent-elles ?\n\nL\'oralité n\'est pas une littérature « mineure ». Elle porte une **mémoire collective**, transmet des valeurs et invente des formes poétiques. L\'étudier, c\'est apprendre à écouter la parole vivante.',NULL,NULL,0.00,1,1,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (6,2,'L\'art du conteur','La performance orale : voix, gestes, rythme et participation du public.','## Le conteur est un acteur\n\nLe conteur ne récite pas un texte : il le **joue**. Sa présence, son regard et son énergie créent une relation directe avec l\'auditoire.\n\n## La voix et le rythme\n\nLa voix se module : elle accélère, ralentit, monte, chuchote. Les pauses servent à créer le suspense et à laisser le public réagir. Le rythme donne corps au récit.\n\n## Le geste et l\'espace\n\nLe conteur utilise ses mains, son corps et l\'espace autour de lui. Un geste peut remplacer une longue description. Le déplacement dans l\'espace marque les changements de scène.\n\n## Le public participe\n\nDans de nombreuses traditions, le public répond, reprend les refrains, pose des questions. Le conte devient alors une **création collective**.\n\n## Transmettre\n\nL\'art du conteur se transmet par l\'écoute et la pratique. Il demande de la mémoire, de l\'improvisation et, surtout, du plaisir à raconter.',NULL,NULL,0.00,0,2,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (7,2,'Proverbes et devinettes','La sagesse condensée : structure, fonctions et usage social des proverbes et des devinettes.','## Le proverbe\n\nLe proverbe est une phrase courte et imagée qui exprime une vérité d\'expérience. Il est anonyme, stable et souvent métaphorique.\n\n## Sa structure\n\nUn proverbe repose fréquemment sur :\n\n- une **image concrète** ;\n- un **parallélisme** ou une **opposition** ;\n- une **formule brève** facile à retenir.\n\n## Ses fonctions\n\n- **Éduquer** : transmettre des valeurs.\n- **Argumenter** : donner du poids à une parole.\n- **Rassembler** : rappeler une expérience commune.\n\n## La devinette\n\nLa devinette propose une énigme dont la réponse est connue de celui qui la pose. Elle stimule l\'observation, la logique et l\'imagination. Elle crée aussi un jeu social.\n\n## À vous de jouer\n\nChoisissez un proverbe de votre région, expliquez son image et montrez dans quelle situation précise on peut l\'employer.',NULL,NULL,0.00,0,3,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (8,2,'Chants traditionnels','Berceuses, chants de travail et chants rituels : la mémoire d\'un peuple en musique.','## Le chant comme mémoire\n\nLe chant accompagne la vie quotidienne : naissance, travail, mariage, deuil, fête. Il conserve des paroles, des langues et des savoirs qui parfois ne s\'écrivent pas.\n\n## Les grands types de chants\n\n- **Les berceuses** apaisent l\'enfant et transmettent l\'affection.\n- **Les chants de travail** rythment l\'effort collectif.\n- **Les chants rituels** accompagnent les cérémonies et les passages de la vie.\n- **Les chants d\'amour et de satire** expriment les émotions et les critiques sociales.\n\n## Poétique du chant\n\nLe chant associe le texte, la mélodie et le rythme. Les répétitions et les refrains facilitent la mémorisation et la participation de tous.\n\n## Le chant aujourd\'hui\n\nLes chants traditionnels se transforment : ils rencontrent la radio, la scène et les nouveaux instruments. Les étudier permet de comprendre comment une culture reste vivante.',NULL,NULL,0.00,0,4,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (9,3,'Narratologie','Les outils d\'analyse du récit : narrateur, focalisation, temps et structure.','## Qu\'est-ce que la narratologie ?\n\nLa narratologie étudie la manière dont un récit est raconté : qui parle, qui voit, à quel rythme et dans quel ordre.\n\n## Le narrateur\n\nLe narrateur n\'est pas l\'auteur. Il peut être **intérieur** à l\'histoire ou **extérieur**. Il peut être omniscient, c\'est-à-dire tout savoir des personnages.\n\n## La focalisation\n\n- **Focalisation zéro** : le narrateur sait tout.\n- **Focalisation interne** : l\'histoire est perçue à travers un personnage.\n- **Focalisation externe** : le narrateur observe sans entrer dans les pensées.\n\n## Le temps du récit\n\nOn distingue le temps de l\'histoire et le temps du récit. Les **analepses** reviennent en arrière, les **prolepses** anticipent. Les accélérations et les ralentissements organisent le rythme.\n\n## La structure narrative\n\nRepérez la situation initiale, l\'élément perturbateur, les péripéties, le dénouement et la situation finale. Cette grille aide à comprendre la logique du récit.',NULL,NULL,0.00,1,1,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (10,3,'Analyse du discours','Étudier un texte comme un acte de communication : énonciation, registres et argumentation.','## Le discours\n\nUn discours est un texte produit par quelqu\'un, pour quelqu\'un, dans une situation donnée. L\'analyse du discours s\'intéresse donc à la fois au texte et à son contexte.\n\n## L\'énonciation\n\nL\'énonciation est l\'acte de produire un énoncé. On repère :\n\n- les **pronoms** (je, tu, nous) ;\n- les **marques de temps** ;\n- les **modalisations** (peut-être, certainement, hélas).\n\n## Les registres\n\nLe registre peut être lyrique, épique, pathétique, comique, satirique ou didactique. Il traduit une intention et une émotion.\n\n## L\'argumentation\n\nUn texte argumentatif défend une thèse avec des arguments et des exemples. Il peut convaincre par la raison ou persuader par l\'émotion.\n\n## Méthode\n\nPour analyser un discours, demandez-vous : **qui parle, à qui, pourquoi, avec quels moyens ?**',NULL,NULL,0.00,0,2,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (11,3,'Sociocritique','Lire le texte dans son contexte : rapports entre littérature, société et pouvoir.','## Origines\n\nLa sociocritique naît de l\'idée que la littérature ne flotte pas au-dessus de la société. Elle est traversée par des valeurs, des conflits et des représentations collectives.\n\n## Principes\n\nLa sociocritique étudie :\n\n- le **contexte de production** de l\'œuvre ;\n- les **représentations sociales** qu\'elle véhicule ;\n- les **rapports de pouvoir** qu\'elle révèle ou conteste.\n\n## Le texte et son contexte\n\nUne œuvre peut confirmer l\'ordre social, le critiquer ou le réinventer. Elle n\'est jamais un simple reflet : elle propose une lecture du monde.\n\n## Applications\n\nOn peut analyser la représentation de la paysannerie, de la ville, de la colonisation, de la famille ou du travail. On compare ensuite cette représentation avec les discours de l\'époque.\n\n## Limites\n\nLa sociocritique ne doit pas réduire l\'œuvre à son contexte. Le texte garde une **forme**, un **style** et une **singularité** qu\'il faut aussi respecter.',NULL,NULL,0.00,0,3,'2026-11-06 16:10:43');
INSERT INTO `course_items` VALUES (12,3,'Méthodes d\'analyse','Commentaire composé, dissertation et fiche de lecture : méthodes pas à pas.','## Trois exercices, trois objectifs\n\n- Le **commentaire composé** analyse un texte précis.\n- La **dissertation** répond à une question en construisant un raisonnement.\n- La **fiche de lecture** synthétise une œuvre pour soi-même.\n\n## Le commentaire composé\n\n1. Lire le texte plusieurs fois et noter ses impressions.\n2. Repérer les procédés : structure, figures, temps, voix.\n3. Formuler deux ou trois axes de lecture.\n4. Rédiger une introduction, un développement organisé et une conclusion.\n\n## La dissertation\n\n1. Analyser le sujet et définir ses termes.\n2. Formuler une problématique.\n3. Construire un plan en deux ou trois parties.\n4. Illustrer chaque argument par un exemple littéraire précis.\n\n## La fiche de lecture\n\nNotez l\'auteur, le titre, la date, le genre, les personnages, l\'intrigue, les thèmes et deux ou trois citations importantes.\n\n## Conseils\n\nTravaillez toujours du **texte vers l\'idée**, jamais l\'inverse. Une analyse juste s\'appuie sur des citations précises et sur une interprétation personnelle.',NULL,NULL,0.00,0,4,'2026-11-06 16:10:43');
/*!40000 ALTER TABLE `course_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id_course` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `objectives` text COLLATE utf8mb4_unicode_ci,
  `level` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `duration` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `semester` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `credits` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ph-book-open',
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_course`),
  UNIQUE KEY `uniq_slug` (`slug`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'LIT301','introduction-a-la-litterature','Introduction à la Littérature','Ce cours présente les bases de l\'analyse littéraire : genres, courants littéraires, figures de style et méthodes d\'interprétation des textes.','Maîtriser les concepts fondamentaux de la littérature\nAnalyser un texte littéraire avec rigueur\nIdentifier les genres et courants littéraires\nRédiger un commentaire composé','Licence 3','45 heures','Pair','6','ph-book-open',NULL,0.00,'published','2026-11-06 16:10:43','2026-11-06 17:27:42');
INSERT INTO `courses` VALUES (2,'LIT401','litterature-orale-africaine','Littérature Orale Africaine','Étude approfondie des genres de la littérature orale africaine : contes, légendes, mythes, proverbes, devinettes et chants traditionnels.','Identifier et classer les genres oraux\nComprendre les fonctions sociales de l\'oraliture\nAnalyser les techniques de narration traditionnelle\nÉtudier la transmission orale et ses enjeux','Master 1','60 heures','Impair','8','ph-feather',NULL,0.00,'published','2026-11-06 16:10:43','2026-11-06 16:10:43');
INSERT INTO `courses` VALUES (3,'LIT302','critique-litteraire','Critique Littéraire','Initiation aux différentes méthodes de critique littéraire : narratologie, analyse du discours, sociocritique et analyse psychocritique.','Maîtriser les outils d\'analyse textuelle\nAppliquer les méthodes de critique\nRédiger une analyse critique argumentée\nÉvaluer les travaux scientifiques en littérature','Licence 3','45 heures','Impair','6','ph-pen-nib',NULL,0.00,'published','2026-11-06 16:10:43','2026-11-07 03:12:13');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id_file` int NOT NULL AUTO_INCREMENT,
  `id_post` int NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` enum('pdf','image','video','doc','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'other',
  `file_size` int DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_free` tinyint(1) NOT NULL DEFAULT '1',
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_file`),
  KEY `id_post` (`id_post`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `posts` (`id_post`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (1,1,'Kpò_ɖe_mɛdé.pdf','pdf',1873948,0.00,1,'uploads/pdf/Kpò_ɖe_mɛdé.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (2,1,'alèkpéhanhou.jpg','image',10413,0.00,1,'alèkpéhanhou.jpg','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (3,2,'Pub 2.pdf','pdf',892474,0.00,1,'uploads/pdf/Pub 2.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (4,3,'Pub 7 syncrétisme.pdf','pdf',2707455,0.00,1,'uploads/pdf/Pub 7 syncrétisme.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (5,3,'eroka.jpg','image',138745,0.00,1,'eroka.jpg','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (6,4,'Pub 10 Immixtion des artistes et polilitique..pdf','pdf',2938192,0.00,1,'uploads/pdf/Pub 10 Immixtion des artistes et polilitique..pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (7,4,'article 2.jpg','image',188359,0.00,1,'article 2.jpg','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (8,5,'article 2.jpg','image',188359,0.00,1,'uploads/images/article 2.jpg','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (9,5,'article 2.jpg','image',188359,0.00,1,'article 2.jpg','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (10,6,'Pub 6 N\'DJEDJOLOKOKO OK 3.pdf','pdf',2714182,0.00,1,'uploads/pdf/Pub 6 N\'DJEDJOLOKOKO OK 3.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (11,6,'image 3.PNG','image',313579,0.00,1,'image 3.PNG','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (12,7,'Pub 8 esthétique et parémie fon.OK4.pdf','pdf',8025253,0.00,1,'uploads/pdf/Pub 8 esthétique et parémie fon.OK4.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (13,7,'image 4.PNG','image',450677,0.00,1,'image 4.PNG','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (14,8,'Article IJCR5.pdf','pdf',761162,0.00,1,'uploads/pdf/Article IJCR5.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (15,8,'ALEPKEYANOU.jpg','image',85263,0.00,1,'ALEPKEYANOU.jpg','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (16,9,'chansons tradirtionelles et figures de style6.pdf','pdf',1042491,0.00,1,'uploads/pdf/chansons tradirtionelles et figures de style6.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (17,9,'image 6A.PNG','image',72637,0.00,1,'image 6A.PNG','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (18,10,'Pub 5 OK7.pdf','pdf',1451584,0.00,1,'uploads/pdf/Pub 5 OK7.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (19,10,'image 7.PNG','image',72704,0.00,1,'image 7.PNG','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (20,11,'Pub 6 N\'DJEDJOLOKOKO OK8.pdf','pdf',2886462,0.00,1,'uploads/pdf/Pub 6 N\'DJEDJOLOKOKO OK8.pdf','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (21,11,'image 8.PNG','image',75661,0.00,1,'image 8.PNG','2026-09-23 14:35:13');
INSERT INTO `files` VALUES (22,12,'Pub  critique révolution9.pdf','pdf',1514398,0.00,1,'uploads/pdf/Pub  critique révolution9.pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (23,12,'image 9.PNG','image',58054,0.00,1,'image 9.PNG','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (24,13,'Pub Hanlo et lohan10.pdf','pdf',2582167,0.00,1,'uploads/pdf/Pub Hanlo et lohan10.pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (25,13,'image 10.PNG','image',65715,0.00,1,'image 10.PNG','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (26,14,'Pub 8 esthétique et parémie fon.OK[1].pdf','pdf',8197629,0.00,1,'uploads/pdf/Pub 8 esthétique et parémie fon.OK[1].pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (27,14,'8.PNG','image',72973,0.00,1,'8.PNG','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (28,15,'Pub 12 Janvier Dénagan.pdf','pdf',1005279,0.00,1,'uploads/pdf/Pub 12 Janvier Dénagan.pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (29,15,'12.PNG','image',73934,0.00,1,'12.PNG','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (30,16,'article 4.pdf','pdf',5684774,0.00,1,'uploads/pdf/article 4.pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (31,16,'aricle 4.PNG','image',379703,0.00,1,'aricle 4.PNG','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (32,17,'article imo kisi.pdf','pdf',1079482,0.00,1,'uploads/pdf/article imo kisi.pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (33,17,'KISI.PNG','image',1417964,0.00,1,'KISI.PNG','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (34,18,'Pub 9 DROITS DE L\'ENFANT.pdf','pdf',1219972,0.00,1,'uploads/pdf/Pub 9 DROITS DE L\'ENFANT.pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (35,18,'ENFANT.PNG','image',73192,0.00,1,'ENFANT.PNG','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (36,19,'Pub 14 Les mots de la souffrance.pdf','pdf',1095121,0.00,1,'uploads/pdf/Pub 14 Les mots de la souffrance.pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (37,19,'Bénin-Supposée.jpg','image',243386,0.00,1,'Bénin-Supposée.jpg','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (38,20,'Pub.4dialogue interreligieux OK.pdf','pdf',1242311,0.00,1,'uploads/pdf/Pub.4dialogue interreligieux OK.pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (39,20,'4PIX.PNG','image',72893,0.00,1,'4PIX.PNG','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (40,21,'pub 1 code de vie .pdf','pdf',1496518,0.00,1,'uploads/pdf/pub 1 code de vie .pdf','2026-09-23 14:35:14');
INSERT INTO `files` VALUES (41,21,'pb 1FIN.PNG','image',72832,0.00,1,'pb 1FIN.PNG','2026-09-23 14:35:14');
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `likes` (
  `id_like` int NOT NULL AUTO_INCREMENT,
  `id_post` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_like`),
  KEY `id_post` (`id_post`),
  KEY `id_user` (`id_user`),
  KEY `ip_address` (`ip_address`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `posts` (`id_post`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `newsletter_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_campaigns` (
  `id_campaign` int unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipients_count` int unsigned NOT NULL DEFAULT '0',
  `sent_count` int unsigned NOT NULL DEFAULT '0',
  `failed_count` int unsigned NOT NULL DEFAULT '0',
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_campaign`),
  KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `newsletter_campaigns` WRITE;
/*!40000 ALTER TABLE `newsletter_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_campaigns` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `newsletter_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_subscribers` (
  `id_subscriber` int NOT NULL AUTO_INCREMENT,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','unsubscribed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `unsubscribe_token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unsubscribed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_subscriber`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_unsubscribe_token` (`unsubscribe_token`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `newsletter_subscribers` WRITE;
/*!40000 ALTER TABLE `newsletter_subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_subscribers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id_post` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `id_category` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `main_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_video` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `embed_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `views` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_post`),
  UNIQUE KEY `slug` (`slug`),
  KEY `id_user` (`id_user`),
  KEY `id_category` (`id_category`),
  KEY `status` (`status`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`id_category`) REFERENCES `categories` (`id_category`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,2,6,'KPO ƉE MƐ DE NǓ NA FIƆ Arstiste : LOUCOU Michel dir ALÈKPÉHANHOU','kpo-de-me-de-n-na-fio-arstiste-loucou-michel-dir-al-kp-hanhou','Etude descriptive d\'une chanson qui met en relief la méchanceté du frère. ','Etude descriptive d\'une chanson qui met en relief la méchanceté du frère. ','alèkpéhanhou.jpg',NULL,NULL,'published',12,'2025-05-08 15:16:00','2026-11-06 18:42:56','2025-05-08 15:16:00');
INSERT INTO `posts` VALUES (2,2,6,'La chanson d’inspiration traditionnelle comme support efficace de  l’enseignement des thèmes et des figures de style …','la-chanson-d-inspiration-traditionnelle-comme-support-efficace-de-l-enseignement-des-themes-et-des-figures-de-style','De nombreuses études ont démontré que les enfants \r\napprennent mieux dans leurs langues. Malgré \r\nl’avantage démontré de l’enseignement des langues \r\nmaternelles à l’école, on rencontre très peu de textes \r\nd’études inspirés très clairement des langues et \r\ncultures africaines. C’est dans le souci de contribuer \r\nà l’approfondissement de cette théorie que nous \r\nproposons de traiter le sujet ainsi libellé. Ce sujet \r\nrepose sur une problématique à plusieurs \r\ninterrogations. A partir d’un corpus de trois textes, \r\nnous entendons démontrer la richesse thématique et \r\nstylistique des chansons béninoises. Les chansons \r\nchoisies sont connues et proviennent d’un artiste \r\ncélèbre qui chante en fongbé, la langue la plus \r\npopulaire du Sud Bénin. Alèkpéhanhou compte plus \r\nde quarante albums dans sa discographie.\r\nMots clés : Bénin, chansons traditionnelles, \r\nstylistique, langues africaines, Alèkpéhanhou,','De nombreuses études ont démontré que les enfants \r\napprennent mieux dans leurs langues. Malgré \r\nl’avantage démontré de l’enseignement des langues \r\nmaternelles à l’école, on rencontre très peu de te',NULL,NULL,NULL,'published',5,'2025-05-08 15:33:00','2026-09-23 15:33:52','2025-05-08 15:33:00');
INSERT INTO `posts` VALUES (3,2,6,'Pratiques syncrétistes dans les chansons traditionnelles modernes dans  l’aire culturelle fon-maxi du Bénin','pratiques-syncretistes-dans-les-chansons-traditionnelles-modernes-dans-l-aire-culturelle-fon-maxi-du-benin','L’échec des missionnaires dans l’effort de christianisation de l’homme noir ou de la propagation de la bonne nouvelle est un lieu commun des manuels d’histoire ainsi que des critiques de la littérature négro-africaine d’expression française, notamment celle de la période des années 50 à 60.\r\n\r\nCe comportement d’infidélité et de foi double est palpable dans les chansons des artistes de l’univers des peuples originaires d’Adja -Tado. À écouter ces artistes chanteurs, ils sont pour la plupart chrétiens. D’ailleurs, ils n’hésitent pas à invoquer les grandes figures de la religion chrétienne dont la Vierge-Marie et Jésus-Christ de Nazareth pour lesquels ils dédient des chansons entières.\r\n\r\nPourtant, à côté de ces textes lumineux, dominent beaucoup d’autres paroles ou textes de chansons totalement opposés à la foi chrétienne.\r\n\r\nD’où notre sujet intitulé : « Pratiques syncrétistes dans les chansons traditionnelles modernes fons et maxis au sud-ouest du Bénin. » Quels sont les éléments matériels et stylistiques de ces pratiques ambiguës ?\r\n\r\nÀ partir de la méthode expérimentale, des outils grammaticaux d’analyse de texte, de la méthode sociocritique, de la démarche scientifique inductive et déductive, nous entendons révéler la manifestation de la double parole dans les chansons d’un corpus constitué de quelques artistes de l’univers culturel fon-maxi.\r\n\r\nCe travail se propose d’éveiller l’attention du large auditoire de ces artistes, un auditoire souvent naïf qui tirerait une conclusion erronée sur la foi de ces icônes de la chanson, juste à l’écoute de quelques pièces au service de la mission évangélisatrice.','L’échec des missionnaires dans l’effort de christianisation de l’homme noir ou de la propagation de la bonne nouvelle est un lieu commun des manuels d’histoire ainsi que des critiques de la littératur','eroka.jpg',NULL,NULL,'published',0,'2025-07-27 15:43:00','2026-09-23 14:35:13','2025-07-27 15:43:00');
INSERT INTO `posts` VALUES (4,2,5,'Processus Démocratiques,  Arts et Littérature en Afrique ','processus-democratiques-arts-et-litterature-en-afrique','Si, pendant la période révolutionnaire, l’artiste n’était pas libre de son inspiration et de l’orientation de sa production, il faut dire qu’après la Conférence des Forces Vives de la Nation de février 1990, les chanteurs béninois ont retrouvé leur liberté avec force et vigueur, devenant ainsi présents sur tous les registres sociétaux.\r\n\r\nLe problème qui se pose ici est de savoir si cette inspiration au service de l’érection des valeurs démocratiques est libre, toujours libre. Les chansons engagées proviennent-elles de la propre et libre volonté de l’artiste ou sont-elles plutôt commandées voire commanditées par des tiers intéressés ou directeurs de consciences ?\r\n\r\nSelon l’hypothèse avancée, les chansons engagées au service de la paix après la Conférence nationale sont rarement libres, tantôt orientées, tantôt « achetées ». Pourquoi ?\r\n\r\nLa réponse à cette question sera le fondement de ce travail fondé sur la méthode quasi-expérimentale et celles relevant de la description puis de la sociocritique.\r\n\r\nL’analyse s’appuiera sur un corpus d’albums ou de textes chantés de deux grands auteurs béninois, Roger Tohon alias Stan Tohon et Loucou Michel dit Alèkpéhanhou.','Si, pendant la période révolutionnaire, l’artiste n’était pas libre de son inspiration et de l’orientation de sa production, il faut dire qu’après la Conférence des Forces Vives de la Nation de févrie','article 2.jpg',NULL,NULL,'published',0,'2025-07-27 15:52:00','2026-09-23 14:35:13','2025-07-27 15:52:00');
INSERT INTO `posts` VALUES (5,2,6,'Processus Démocratiques,  Arts et Littérature en Afrique','processus-democratiques-arts-et-litterature-en-afrique-2','Si, pendant la période révolutionnaire, l’artiste n’était pas libre de son inspiration et de l’orientation de sa production, il faut dire qu’après la Conférence des Forces Vives de la Nation de février 1990, les chanteurs béninois ont retrouvé leur liberté avec force et vigueur, devenant ainsi présents sur tous les registres sociétaux.\r\n\r\nLe problème qui se pose ici est de savoir si cette inspiration au service de l’érection des valeurs démocratiques est libre, toujours libre. Les chansons engagées proviennent-elles de la propre et libre volonté de l’artiste ou sont-elles plutôt commandées voire commanditées par des tiers intéressés ou directeurs de consciences ?\r\n\r\nSelon l’hypothèse avancée, les chansons engagées au service de la paix après la Conférence nationale sont rarement libres, tantôt orientées, tantôt « achetées ». Pourquoi ?\r\n\r\nLa réponse à cette question sera le fondement de ce travail fondé sur la méthode quasi-expérimentale et celles relevant de la description puis de la sociocritique.\r\n\r\nL’analyse s’appuiera sur un corpus d’albums ou de textes chantés de deux grands auteurs béninois, Roger Tohon alias Stan Tohon et Loucou Michel dit Alèkpéhanhou.','Si, pendant la période révolutionnaire, l’artiste n’était pas libre de son inspiration et de l’orientation de sa production, il faut dire qu’après la Conférence des Forces Vives de la Nation de févrie','article 2.jpg',NULL,NULL,'published',0,'2025-07-27 15:54:00','2026-09-23 14:35:13','2025-07-27 15:54:00');
INSERT INTO `posts` VALUES (6,2,6,'APPROCHE ESTHETIQUE DE LA CHANSON  TRADITIONNELLE BENINOISE A TRAVERS  L’ANALYSE STYLISTIQUE DE « N’DJE  DJOLOKOKO » D’HONORE BLEKPON','approche-esthetique-de-la-chanson-traditionnelle-beninoise-a-travers-l-analyse-stylistique-de-n-dje-djolokoko-d-honore-blekpon','La chanson traditionnelle en République du Bénin s’enrichit au fil des jours de techniques et de procédés qui en renforcent le développement thématique et esthétique.\r\n\r\nPour répondre aux besoins des Béninois de s’enraciner dans leurs cultures tout en se projetant dans le monde moderne, les chanteurs procèdent de plus en plus à des créations ou à des récupérations de formes et d’instruments. Ceux-ci assurent, à la fois, un ancrage socioculturel de leurs œuvres et leur ouvrent des perspectives prometteuses.\r\n\r\nL’artiste chanteur Honoré Blèkpon s’inscrit dans cette perspective en apportant des touches thématique et stylistique à un ancien rythme d’orchestre appelé Djègbé et dont Yédénou Adjahoui serait le fondateur dans les années 1950.\r\n\r\nQue retenir de l\'esthétique traditionnelle de ce rythme ? Qui en sont les grandes figures de production ? De « Gbê Ma Houé Fidé » à « N\'djè djolokoko », quelles sont les innovations observées ? Que pourrait-on retenir de l’utilité de l\'apport de ces nouveautés par l\'artiste Blèkpon ?\r\n\r\nCes interrogations sont les pistes qui nous permettent d\'énoncer deux hypothèses plausibles. D’abord, les innovations dans la chanson traditionnelle « N\'djè djolokoko » du rythme Djègbé de l\'artiste béninois Honoré Blèkpon sont d’ordre thématique, technique, sociolinguistique et stylistique.\r\n\r\nEnsuite, l’importance de ces innovations tient à un souci d’apport esthétique de l\'artiste et à un besoin de satisfaction du désir de l\'auditoire ou de la clientèle.\r\n\r\nAu regard de l’héritage historique et artistique du rythme, il est intéressant de rechercher les niveaux de concrétisation des innovations que Blèkpon apporte à l’exécution d’un rythme pratiqué chez les Gun au Bénin lors des cérémonies traditionnelles/funéraires.\r\n\r\nPartant du postulat selon lequel l’artiste-chanteur Blèkpon imprime des touches innovantes au rythme Djègbé, cette contribution étudie les niveaux de réalisation desdites innovations en prenant appui sur un corpus comparatif de deux chansons : l’une de l’« ancêtre » fondateur du rythme « Gbê Ma Houé Fidé » de Yedenou Adjahoui, et l’autre, « N\'djè djolokoko » de Honoré Blèkpon.','La chanson traditionnelle en République du Bénin s’enrichit au fil des jours de techniques et de procédés qui en renforcent le développement thématique et esthétique.\r\n\r\nPour répondre aux besoins des ','image 3.PNG',NULL,NULL,'published',0,'2025-07-27 16:14:00','2026-09-23 14:35:13','2025-07-27 16:14:00');
INSERT INTO `posts` VALUES (7,2,6,'L’ÉDUCATION MULTILINGUE,  UNE NÉCESSITÉ POUR TRANSFORMER  L’ÉDUCATION','l-ducation-multilingue-une-n-cessit-pour-transformer-l-ducation','La parémie est à la fois un fait social et littéraire commun à tous les groupes socio-culturels. Il en est de même de l’univers fon et apparentés dont la parémie se particularise cependant par une disposition syntaxique avec, dans certains cas, des visées stylistique et esthétique qui méritent d’être regardées de près.\r\n\r\nCet article en révèle les dispositions et le mode de fonctionnement tant dans le fond que dans la forme. Le développement a été mené dans un moule au confluent méthodologique multiple : la méthode expérimentale ou quasi expérimentale, la sociolinguistique, la sociocritique et la poétique.\r\n\r\nSi la première partie aborde les dispositions communes à toutes les parémies, la deuxième en montre les parémies à disposition binaire, tandis que la troisième partie en révèle les particularités formelles, spécifiquement littéraires, qui privilégient l’esthétique de certaines parémies sur leurs fonctions premières.','La parémie est à la fois un fait social et littéraire commun à tous les groupes socio-culturels. Il en est de même de l’univers fon et apparentés dont la parémie se particularise cependant par une dis','image 4.PNG',NULL,NULL,'published',0,'2025-07-27 16:21:00','2026-09-23 14:35:13','2025-07-27 16:21:00');
INSERT INTO `posts` VALUES (8,2,6,'LES MOTS DE LA SOUFFRANCE : ANALYSE EXPLORATOIRE DE LA RHÉTORIQUE DE LA DOULEUR CHEZ ALÈKPÉHANHOU ','les-mots-de-la-souffrance-analyse-exploratoire-de-la-rh-torique-de-la-douleur-chez-al-kp-hanhou','À la fois funéraire et funèbre, le rythme Zɛnli a été créé pour célébrer les défunts sur le plateau d’Abomey en République du Bénin.\r\n\r\nBien qu\'il s’inscrive parmi les rythmes populaires, il a été rénové par Alèkpéhanhou qui en a tout de même conservé la dimension funèbre. L’évocation constante du thème de la mort dans ses albums a motivé l\'étude de la rhétorique de l’isotopie de la douleur.\r\n\r\nL\'analyse de son répertoire révèle que l\'expression de la douleur se manifeste à travers plusieurs axes de construction, reposant sur divers procédés esthétiques.\r\n\r\nEn combinant les perspectives de la linguistique, de la sémiologie et de la stylistique, cette étude examine les procédés esthétiques qui favorisent la construction du réseau isotopique de la douleur chez Alèkpéhanhou.','À la fois funéraire et funèbre, le rythme Zɛnli a été créé pour célébrer les défunts sur le plateau d’Abomey en République du Bénin.\r\n\r\nBien qu\'il s’inscrive parmi les rythmes populaires, il a été rén','ALEPKEYANOU.jpg',NULL,NULL,'published',0,'2025-07-27 16:34:00','2026-09-23 14:35:13','2025-07-27 16:34:00');
INSERT INTO `posts` VALUES (9,2,6,'La chanson d’inspiration traditionnelle comme support efficace de  l’enseignement des thèmes et des figures de style ','la-chanson-d-inspiration-traditionnelle-comme-support-efficace-de-l-enseignement-des-themes-et-des-figures-de-style-2','De nombreuses études ont démontré que les enfants apprennent mieux dans leurs langues.\r\n\r\nMalgré l’avantage démontré de l’enseignement des langues maternelles à l’école, on rencontre très peu de textes d’études inspirés très clairement des langues et cultures africaines.\r\n\r\nC’est dans le souci de contribuer à l’approfondissement de cette théorie que nous proposons de traiter le sujet ainsi libellé.\r\n\r\nCe sujet repose sur une problématique à plusieurs interrogations.\r\n\r\nÀ partir d’un corpus de trois textes, nous entendons démontrer la richesse thématique et stylistique des chansons béninoises.\r\n\r\nLes chansons choisies sont connues et proviennent d’un artiste célèbre qui chante en fongbé, la langue la plus populaire du Sud Bénin.\r\n\r\nAlèkpéhanhou compte plus de quarante albums dans sa discographie.','De nombreuses études ont démontré que les enfants apprennent mieux dans leurs langues.\r\n\r\nMalgré l’avantage démontré de l’enseignement des langues maternelles à l’école, on rencontre très peu de texte','image 6A.PNG',NULL,NULL,'published',0,'2025-07-28 13:31:00','2026-09-23 14:35:13','2025-07-28 13:31:00');
INSERT INTO `posts` VALUES (10,2,6,'LES CHANSONS D’ADEDOYIN : UN VERITABLE   PLAIDOYER POUR LA CAUSE FEMININE ','les-chansons-d-adedoyin-un-veritable-plaidoyer-pour-la-cause-feminine','Dans l’expression de son ressenti personnel et de celui de son peuple, l’artiste chanteur s’engage doublement à magnifier les valeurs et à dénoncer les travers de société.\r\n\r\nDans le lot des thèmes de société abordés par les chanteurs traditionnels modernes béninois, une place spéciale est réservée à la femme.\r\n\r\nSi la plupart des chansons sur le sujet s’appliquent à peindre les travers de la femme pour inviter les hommes à la prudence à son sujet, il s’en trouve quand même quelques-unes, certes rares, qui lui viennent au secours en vantant à merveille ses mérites.\r\n\r\nC’est le cas d’Adédoyin de Dassa, objet de notre étude.\r\n\r\nPourquoi cet artiste choisit-il de se particulariser ? Par quels moyens esthétiques Adédoyin exalte-t-il la femme ?\r\n\r\nPour répondre à cette préoccupation, nous avons analysé un corpus de chansons relevant de la production discographique de l’artiste chanteur originaire de Dassa.\r\n\r\nPour révéler sa vision de la femme, les méthodes expérimentale, descriptive et la grammaire de texte ont été principalement sollicitées.\r\n\r\nDans la logique de la déclaration largement répandue selon laquelle l’homme est double, l’artiste Adédoyin en fait une application concrète en révélant l’autre femme, la femme merveilleuse, miséricordieuse, la femme digne d’éloge, en opposition à l’autre facette récurrente.\r\n\r\nDe l’argumentaire développé autour de la femme, on retient donc clairement que celle-ci n’est pas que diabolique, elle peut être aussi angélique.','Dans l’expression de son ressenti personnel et de celui de son peuple, l’artiste chanteur s’engage doublement à magnifier les valeurs et à dénoncer les travers de société.\r\n\r\nDans le lot des thèmes de','image 7.PNG',NULL,NULL,'published',0,'2025-07-28 13:56:00','2026-09-23 14:35:13','2025-07-28 13:56:00');
INSERT INTO `posts` VALUES (11,2,6,'APPROCHE ESTHETIQUE DE LA CHANSON  TRADITIONNELLE BENINOISE A TRAVERS  L’ANALYSE STYLISTIQUE DE « N’DJE  DJOLOKOKO » D’HONORE BLEKPON ','approche-esthetique-de-la-chanson-traditionnelle-beninoise-a-travers-l-analyse-stylistique-de-n-dje-djolokoko-d-honore-blekpon-2','La chanson traditionnelle en République du Bénin s’enrichit au fil des jours de techniques et de procédés qui en renforcent le développement thématique et esthétique.\r\n\r\nPour répondre aux besoins des Béninois de s’enraciner dans leurs cultures tout en se projetant dans le monde moderne, les chanteurs procèdent de plus en plus à des créations ou à des récupérations de formes et d’instruments. Ceux-ci assurent, à la fois, un ancrage socioculturel de leurs œuvres et leur ouvrent des perspectives prometteuses.\r\n\r\nL’artiste chanteur Honoré Blèkpon s’inscrit dans cette perspective en apportant des touches thématique et stylistique à un ancien rythme d’orchestre appelé Djègbé, et dont Yédénou Adjahoui serait le fondateur dans les années 1950.\r\n\r\nQue retenir de l\'esthétique traditionnelle de ce rythme ? Qui en sont les grandes figures de production ? De « Gbê Ma Houé Fidé » à « N\'djè djolokoko », quelles sont les innovations observées ? Que pourrait-on retenir de l’utilité de l\'apport de ces nouveautés par l\'artiste Blèkpon ?\r\n\r\nCes interrogations sont les pistes qui nous permettent d\'énoncer deux hypothèses plausibles. D’abord, les innovations dans la chanson traditionnelle « N\'djè djolokoko » du rythme Djègbé de l\'artiste béninois Honoré Blèkpon sont d’ordre thématique, technique, sociolinguistique et stylistique.\r\n\r\nEnsuite, l’importance de ces innovations tient à un souci d’apport esthétique de l\'artiste et à un besoin de satisfaction du désir de l\'auditoire ou de la clientèle.\r\n\r\nAu regard de l’héritage historique et artistique du rythme, il est intéressant de rechercher les niveaux de concrétisation des innovations que Blèkpon apporte à l’exécution d’un rythme pratiqué chez les Gun au Bénin, lors des cérémonies traditionnelles/funéraires.\r\n\r\nPartant du postulat selon lequel l’artiste-chanteur Blèkpon imprime des touches innovantes au rythme Djègbé, cette contribution étudie les niveaux de réalisation desdites innovations en prenant appui sur un corpus comparatif de deux chansons : l’une de l’« ancêtre » fondateur du rythme, « Gbê Ma Houé Fidé » de Yédénou Adjahoui, et l’autre, « N\'djè djolokoko » de Honoré Blèkpon.','La chanson traditionnelle en République du Bénin s’enrichit au fil des jours de techniques et de procédés qui en renforcent le développement thématique et esthétique.\r\n\r\nPour répondre aux besoins des ','image 8.PNG',NULL,NULL,'published',0,'2025-07-28 14:02:00','2026-09-23 14:35:13','2025-07-28 14:02:00');
INSERT INTO `posts` VALUES (12,2,6,'PRODUCTIONS ESTHÉTIQUES ET CRITIQUE   POLITIQUE SOUS LE SYSTEME RÉVOLUTIONNAIRE   AU BÉNIN : ENTRE COLLABORATION ET RÉSISTANCE,','productions-esth-tiques-et-critique-politique-sous-le-systeme-r-volutionnaire-au-b-nin-entre-collaboration-et-r-sistance','Au Bénin, les productions littéraires sont caractérisées par des périodes liées chacune à une idéologie ou tendance.\r\n\r\nCet article se propose d’analyser l’effervescence de l’esprit créateur sous la période révolutionnaire qui s’étend de 1972 à 1990.\r\n\r\nQuelle est l’atmosphère qui a prévalu pendant la période révolutionnaire, contraignant certains artistes et écrivains à collaborer pendant que d’autres ont tenté de résister à l’étouffement des libertés d’action et de création des œuvres de l’esprit ?\r\n\r\nPar quels moyens les créateurs d’œuvres de l’esprit ont-ils résisté à l’extrême censure en vogue à l’époque du Parti de la Révolution populaire du Bénin ?\r\n\r\nLe traitement de ce sujet nous conduira à solliciter un appareillage méthodologique à plusieurs paliers : l’enquête de terrain, la méthode expérimentale, la sociocritique, le compte rendu de lecture et d’écoute de quelques chansons de la période révolutionnaire.\r\n\r\nÀ la vérité, le système révolutionnaire a régenté énergiquement les productions artistiques et littéraires, mais il y a eu quelques chansons et textes d’expression de la liberté de créer et d’échapper à la politique d’embrigadement mise en place sous le régime militaro-marxiste dirigé par le Général Mathieu Kérékou.','Au Bénin, les productions littéraires sont caractérisées par des périodes liées chacune à une idéologie ou tendance.\r\n\r\nCet article se propose d’analyser l’effervescence de l’esprit créateur sous la p','image 9.PNG',NULL,NULL,'published',2,'2025-07-28 14:08:00','2026-11-06 19:12:35','2025-07-28 14:08:00');
INSERT INTO `posts` VALUES (13,2,6,'Hanlo (chanson-proverbe) et lohan (proverbe-chanson): mêmes constituants pour deux  énoncés différents ','hanlo-chanson-proverbe-et-lohan-proverbe-chanson-memes-constituants-pour-deux-enonces-differents','La parémie, essentielle à l’esthétique orale au Bénin, notamment dans l\'univers Fon, présente deux types de compositions distincts : lǒhaǹ (proverbe-chanson) et hanló (chanson proverbiale).\r\n\r\nBien que similaires, ces concepts expriment deux réalités différentes.\r\n\r\nCet article vise à dissiper la confusion entre ces termes en décrivant leurs structures et modes opératoires.\r\n\r\nEn utilisant une approche quasi-expérimentale, sociocritique et sémiologique, l\'étude analyse deux chansons de Loucou Michel, dit Alèkpéhanhou, pour démontrer la distinction entre hanló et lǒhaǹ, ainsi que leurs moyens expressifs.\r\n\r\nLes résultats devraient clarifier les différences et souligner l\'importance de la parémie comme ingrédient privilégié dans la création de belles et bonnes paroles chantées, vitales pour l\'esthétique béninoise.','La parémie, essentielle à l’esthétique orale au Bénin, notamment dans l\'univers Fon, présente deux types de compositions distincts : lǒhaǹ (proverbe-chanson) et hanló (chanson proverbiale).\r\n\r\nBien qu','image 10.PNG',NULL,NULL,'published',0,'2025-07-28 14:16:00','2026-09-23 14:35:14','2025-07-28 14:16:00');
INSERT INTO `posts` VALUES (14,2,6,'L’esthétique de la parémie en milieu fon  et apparentés','l-esthetique-de-la-paremie-en-milieu-fon-et-apparentes','La parémie est à la fois un fait social et littéraire commun à tous les groupes socio-culturels. Il en est de même de l’univers fon et apparentés dont la parémie se particularise cependant par une disposition syntaxique avec, dans certains cas, des visées stylistiques et esthétiques qui méritent d’être regardées de près.\r\n\r\nCet article en révèle les dispositions et le mode de fonctionnement tant dans le fond que dans la forme. Le développement a été mené dans un moule au confluent méthodologique multiple : la méthode expérimentale ou quasi expérimentale, la sociolinguistique, la sociocritique et la poétique.\r\n\r\nSi la première partie aborde les dispositions communes à toutes les parémies, la deuxième en montre les parémies à disposition binaire tandis que la troisième partie en révèle les particularités formelles, spécifiquement littéraires qui privilégient l’esthétique de certaines parémies sur leurs fonctions premières.','La parémie est à la fois un fait social et littéraire commun à tous les groupes socio-culturels. Il en est de même de l’univers fon et apparentés dont la parémie se particularise cependant par une dis','8.PNG',NULL,NULL,'published',0,'2025-09-25 22:24:00','2026-09-23 14:35:14','2025-09-25 22:24:00');
INSERT INTO `posts` VALUES (15,2,6,'Structure et fonctions des olǒ ou dictons proverbiaux dans les chansons de  denagan janvier honfo ','structure-et-fonctions-des-olo-ou-dictons-proverbiaux-dans-les-chansons-de-denagan-janvier-honfo','Cet article se penche sur un aspect souvent négligé de la musique béninoise contemporaine : l\'utilisation des dictons proverbiaux, appelés OLǑ, dans les chansons de l\'artiste bénino-allemand Dénagan Janvier Honfo.\r\n\r\nAlors que cette pratique, connue sous le nom de « hanló », a été une caractéristique essentielle de la musique d\'inspiration traditionnelle au Bénin, elle est devenue moins visible parmi les jeunes artistes.\r\n\r\nNotre recherche vise à explorer comment Honfo utilise ces proverbes du terroir pour créer une esthétique musicale unique. Nous formulons des hypothèses sur la manière dont ces dictons sont intégrés dans ses chansons et comment ils contribuent à la richesse thématique et stylistique de son œuvre.\r\n\r\nEn utilisant une méthodologie quasi-expérimentale et une analyse de contenu approfondie, nous examinons les différentes fonctions des OLǑ dans les chansons de Honfo, mettant ainsi en lumière l\'importance de cette tradition dans la musique béninoise contemporaine.','Cet article se penche sur un aspect souvent négligé de la musique béninoise contemporaine : l\'utilisation des dictons proverbiaux, appelés OLǑ, dans les chansons de l\'artiste bénino-allemand Dénagan ','12.PNG',NULL,NULL,'published',0,'2025-09-25 22:29:00','2026-09-23 14:35:14','2025-09-25 22:29:00');
INSERT INTO `posts` VALUES (16,2,6,'LA FEMME, OBJET TRAINE, OBJET DE MORT :  DEMONSTRATION A TRAVERS QUELQUES PIECES  CHANTEES DES ARTISTES FON ET MAXI DU BENIN ','la-femme-objet-traine-objet-de-mort-demonstration-a-travers-quelques-pieces-chantees-des-artistes-fon-et-maxi-du-benin','Tout le monde connaît la chanson de Cookie Dingler, « Ne la laisse pas tomber, elle est si fragile, être une femme libérée, tu sais, c’est pas si facile ».\r\nCette chanson pose nettement la problématique de la libération de la femme.\r\n\r\nLa femme peut-elle se libérer ? Ou encore, faut-il l’aider à se libérer ? Si oui, à quelle hauteur ?\r\n\r\nL’objectif de cet article est de rapporter l’image que les artistes traditionnels de la chanson se font de la femme et la manipulation qu’ils font du vocable désignatif de la femme en langue fon.\r\n\r\nEn effet, ce vocable prévient implicitement du malheur qui pourrait provenir de la collaboration avec ce sexe (« nyɔnnu » qui signifie littéralement « sache boire »).\r\n\r\nPour mener notre analyse, nous nous sommes fondé sur un corpus de chansons de quelques chanteurs reconnus dont Alèkpéhanhou, Alokpon et Lètriki, originaires de trois départements différents.\r\n\r\nLa grammaire de texte, les méthodes déductive et inductive ainsi que la sociocritique et la psychanalyse nous ont servi d’outils méthodologiques pour parvenir au résultat présenté en deux parties : la femme considérée comme la source des maux, d’une part, et la femme considérée comme la grande faucheuse, d’autre part.','Tout le monde connaît la chanson de Cookie Dingler, « Ne la laisse pas tomber, elle est si fragile, être une femme libérée, tu sais, c’est pas si facile ».\r\nCette chanson pose nettement la problématiq','aricle 4.PNG',NULL,NULL,'published',0,'2025-10-05 20:03:00','2026-09-23 14:35:14','2025-10-05 20:03:00');
INSERT INTO `posts` VALUES (17,2,6,'La perception négative de l’artiste dans les chansons traditionnelles du  Bénin ','la-perception-negative-de-l-artiste-dans-les-chansons-traditionnelles-du-benin','La condition de l’artiste est un thème universel, récurrent dans les productions artistiques et littéraires, quelle que soit l’aire socioculturelle considérée.\r\n\r\nAu Bénin, nous constatons aussi la permanence de ce thème dans les chansons des artistes d’inspiration traditionnelle.\r\n\r\nAlors, quelques questions se posent. Quelle est la perception sociale de l’artiste ? Quelles images l’artiste chansonnier donne-t-il à voir de lui-même dans ses propres productions littéraires ? Par quels procédés esthétiques y parvient-il ?\r\n\r\nIl est plausible que le regard de l’artiste sur lui-même participe d’une démarche autoréflexive d’exorcisme et de purgation dont l’expression puise abondamment dans le lyrisme et la tonalité épique propres à certaines paroles (ou « genres ») de la littérature orale.\r\n\r\nEn nous inspirant des démarches et des concepts de la sociocritique, de la critique psychanalytique, de la démarche expérimentale suivie de l’analyse fondée sur les méthodes déductive et inductive, nous avons essayé, sur la base d’un corpus de chansons produits par des artistes contemporains, dont Alokpon, Ayékoto, Ezin Gangnon et Kiri Kanta, d’inventorier les images de l’artiste rapportées par le chanteur dans ses propres productions, d’une part, et celles qu’il se donne lui-même au sujet de sa condition d’artiste et qu’il apporte en réplique ou en confirmation, d’autre part.','La condition de l’artiste est un thème universel, récurrent dans les productions artistiques et littéraires, quelle que soit l’aire socioculturelle considérée.\r\n\r\nAu Bénin, nous constatons aussi la pe','KISI.PNG',NULL,NULL,'published',0,'2025-10-05 20:14:00','2026-09-23 14:35:14','2025-10-05 20:14:00');
INSERT INTO `posts` VALUES (18,2,6,'L’EXPRESSION DES DROITS DE L’ENFANT A TRAVERS LE  PRENOM CHEZ LES FᴐN ET APPARENTES DU SUD-BENIN ','l-expression-des-droits-de-l-enfant-a-travers-le-prenom-chez-les-f-n-et-apparentes-du-sud-benin','En milieu fᴐn et apparentés, l’enfant est un bien, le bien le plus précieux.\r\n\r\nCette conscience que ces populations ont de la valeur de ce bien, elles l’extériorisent à travers les prénoms attribués à leur progéniture.\r\n\r\nEn quoi ces prénoms sont-ils révélateurs de tous les soins accordés à l’enfance et, par ricochet, des droits obligatoires à lui dédiés ?\r\n\r\nCette recherche se propose, dans une démarche déductive, de partir du prénom comme postulat, pour démontrer, qu’en milieu fᴐn et apparentés, les droits de l’enfant tiennent une place d’honneur.\r\n\r\nL’analyse des données cumulées lors de la recherche documentaire et de l’enquête de terrain permet d’affirmer que chez les Fᴐn et apparentés, certains prénoms sont porteurs d’espoir et chargés du rêve de leurs géniteurs.\r\n\r\nLa réalisation d’un tel rêve suppose d’abord que l’enfant soit dignement élevé afin de pouvoir porter le projet de ceux qui l’ont prénommé.\r\n\r\nD’où la nécessité de respecter et de préserver les droits de l’enfant.','En milieu fᴐn et apparentés, l’enfant est un bien, le bien le plus précieux.\r\n\r\nCette conscience que ces populations ont de la valeur de ce bien, elles l’extériorisent à travers les prénoms attribués ','ENFANT.PNG',NULL,NULL,'published',0,'2025-10-05 20:30:00','2026-09-23 14:35:14','2025-10-05 20:30:00');
INSERT INTO `posts` VALUES (19,2,6,'LES MOTS DE LA SOUFFRANCE: ANALYSE EXPLORATOIRE DE LA RHÉTORIQUE DE LA DOULEUR CHEZ ALÈKPÉHANHOU ','les-mots-de-la-souffrance-analyse-exploratoire-de-la-rh-torique-de-la-douleur-chez-al-kp-hanhou-2','À la fois funéraire et funèbre, le rythme Zenli a été créé pour célébrer les défunts sur le plateau d’Abomey en République du Bénin. Bien qu’il s’inscrive parmi les rythmes populaires, il a été rénové par Alèkpèhanhou qui en a tout de même conservé la dimension funèbre.\r\n\r\nL’évocation constante du thème de la mort dans ses albums a motivé l’étude de la rhétorique de l’isotopie de la douleur. L’analyse de son répertoire révèle que l’expression de la douleur se manifeste à travers plusieurs niveaux de construction, reposant sur divers procédés esthétiques.\r\n\r\nEn combinant les perspectives de la linguistique, de la sémiologie et de la stylistique, cette étude examine les procédés esthétiques qui favorisent la construction du réseau isotopique de la douleur chez Alèkpèhanhou.','À la fois funéraire et funèbre, le rythme Zenli a été créé pour célébrer les défunts sur le plateau d’Abomey en République du Bénin. Bien qu’il s’inscrive parmi les rythmes populaires, il a été rénové','Bénin-Supposée.jpg',NULL,NULL,'published',1,'2025-10-06 18:53:00','2026-09-23 20:07:40','2025-10-06 18:53:00');
INSERT INTO `posts` VALUES (20,2,6,'THEMATIQUE 2 : CRISES SOCIALES EN AFRIQUE ET REPONSES  ENDOGENES ACTUELLES ','thematique-2-crises-sociales-en-afrique-et-reponses-endogenes-actuelles','À partir de l’exemple de la Conférence épiscopale du Bénin, les auteurs analysent les rôles des religions et des autorités religieuses dans la démocratie béninoise et dans la consolidation de la paix sociale depuis l’historique Conférence nationale de février 1990 jusqu’au pouvoir de Boni Yayi en 2010.\r\n\r\nDepuis 1990, la volonté irréductible du religieux de se mêler des affaires de la société béninoise transparaît dans toutes les phases de l’histoire du Renouveau démocratique.\r\n\r\nLes séances et campagnes de prières, les tentatives de calmer les tensions politiques et sociales, les déclarations remarquables au nom des religions, à différents niveaux et à différentes circonstances, sur la vie sociale du pays jusqu’à l’arbitrage des autorités religieuses à l’occasion de vives tensions politiques et sociales sont autant d’actions que posent les religions en faveur de la consolidation de la paix.\r\n\r\nCette analyse se veut une interprétation fondée sur des actions collectives, parfois isolées, et les commentaires qu’elles appellent.','À partir de l’exemple de la Conférence épiscopale du Bénin, les auteurs analysent les rôles des religions et des autorités religieuses dans la démocratie béninoise et dans la consolidation de la paix ','4PIX.PNG',NULL,NULL,'published',2,'2025-10-06 19:05:00','2026-11-06 16:16:13','2025-10-06 19:05:00');
INSERT INTO `posts` VALUES (21,2,6,'PLAIDOYER POUR LE RESPECT DU CODE DE VIE   À TRAVERS LES CHANSONS   D’ALÈKPÉHANHOU ET D’ALOKPON ','plaidoyer-pour-le-respect-du-code-de-vie-travers-les-chansons-d-al-kp-hanhou-et-d-alokpon','Dans la catégorie des paroles esthétiques narratives, la chanson béninoise fonctionne comme la parole littéraire la plus dynamique. C’est en ses entrailles que les peuples du bloc fɔ̀n-maxi cristallisent leurs vécus et codes sociaux. Dignes héritiers de cette culture, Alèkpéhanhou et Alokpon en font eux aussi un domaine privilégié d’inspiration.\r\n\r\nPour quelles raisons ces artistes invitent-ils expressément au respect du code de vie ? La présente étude nourrit l’objectif de montrer la priorité que Alèkpéhanhou et Alokpon accordent au respect des bonnes mœurs héritées des ancêtres, à travers leurs productions chantées.\r\n\r\nPour atteindre les résultats escomptés, la démarche méthodologique privilégiée tient en un palier à quatre niveaux : la recherche documentaire, l’enquête de terrain, l’analyse des informations puis la sociocritique.\r\n\r\nL’anthropologie juridique fɔ̀n et maxi présente un organigramme qui s’échelonne du xwe (enclos parental) jusqu’au to (pays, royaume, nation…). À chaque niveau de l’organisation sociale sont établis des su (interdits ou lois) qui permettent d’organiser les vies familiale et communautaire chez les Fɔ̀n et Maxi. Les lois sont régulatrices de la vie de toute la société pour maintenir l’harmonie.\r\n\r\nAlèkpéhanhou et Alokpon œuvrent à la vulgarisation et au respect de ces interdits à travers leurs productions chantées.','Dans la catégorie des paroles esthétiques narratives, la chanson béninoise fonctionne comme la parole littéraire la plus dynamique. C’est en ses entrailles que les peuples du bloc fɔ̀n-maxi cristallis','pb 1FIN.PNG',NULL,NULL,'published',8,'2025-10-06 19:11:00','2026-11-06 21:52:08','2025-10-06 19:11:00');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `purchase_recovery_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_recovery_requests` (
  `id_request` int unsigned NOT NULL AUTO_INCREMENT,
  `email_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_request`),
  KEY `idx_email` (`email_hash`,`created_at`),
  KEY `idx_ip` (`ip_hash`,`created_at`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `purchase_recovery_requests` WRITE;
/*!40000 ALTER TABLE `purchase_recovery_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_recovery_requests` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `id_purchase` int NOT NULL AUTO_INCREMENT,
  `id_file` int DEFAULT NULL,
  `id_course` int unsigned DEFAULT NULL,
  `id_item` int unsigned DEFAULT NULL,
  `buyer_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XOF',
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kkiapay',
  `transaction_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `download_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_purchase`),
  UNIQUE KEY `uniq_provider_tx` (`provider`,`transaction_id`),
  KEY `id_file` (`id_file`),
  KEY `status` (`status`),
  KEY `download_token` (`download_token`),
  KEY `idx_course` (`id_course`),
  KEY `idx_item` (`id_item`),
  CONSTRAINT `fk_purchase_course` FOREIGN KEY (`id_course`) REFERENCES `courses` (`id_course`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchase_item` FOREIGN KEY (`id_item`) REFERENCES `course_items` (`id_item`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id_setting` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_setting`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_title','Joie Enseignante');
INSERT INTO `settings` VALUES (2,'site_description','Plateforme pédagogique pour enseignants et étudiants');
INSERT INTO `settings` VALUES (3,'site_logo','');
INSERT INTO `settings` VALUES (4,'items_per_page','10');
INSERT INTO `settings` VALUES (5,'comments_moderation','1');
INSERT INTO `settings` VALUES (6,'allow_registration','1');
INSERT INTO `settings` VALUES (7,'site_email','contact@joie-enseignante.com');
INSERT INTO `settings` VALUES (8,'site_phone','+229 XX XX XX XX');
INSERT INTO `settings` VALUES (9,'site_address','Cotonou, Bénin');
INSERT INTO `settings` VALUES (10,'social_facebook','https://facebook.com/joiedenseignante');
INSERT INTO `settings` VALUES (11,'social_twitter','https://twitter.com/joiedenseignante');
INSERT INTO `settings` VALUES (12,'social_linkedin','https://linkedin.com/company/joiedenseignante');
INSERT INTO `settings` VALUES (13,'social_youtube','https://youtube.com/@joiedenseignante');
INSERT INTO `settings` VALUES (14,'social_instagram','https://instagram.com/joiedenseignante');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `user_downloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_downloads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_file` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_download` (`id_user`,`id_file`),
  KEY `id_user` (`id_user`),
  KEY `id_file` (`id_file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_downloads` WRITE;
/*!40000 ALTER TABLE `user_downloads` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_downloads` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin','teacher','auteur','etudiant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `idx_users_reset_token` (`reset_token`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Sylvestre Djouamon','prof@joieenseignante.com','$2y$10$clegx55ose97jnn5TmKXF.KI9O4M32WJr/KvC8Q0qRSF559dvlQnS','admin',NULL,'Professeur de littérature, Département de Littérature, Université de Cotonou.','2026-09-23 14:35:13','2026-11-07 08:44:08',1,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

