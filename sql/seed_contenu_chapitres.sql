-- ============================================================
-- Seed : contenu de démonstration des chapitres
-- Projet : Joie Enseignante
-- À exécuter APRÈS sql/migration_contenu_chapitre.sql
-- ============================================================
SET NAMES utf8mb4;

UPDATE `course_items` SET
    `description` = 'Définition, fonctions et approches de la littérature : un art du langage qui interroge l''homme et la société.',
    `content` = '## Une définition ouverte

La littérature est l''ensemble des œuvres écrites ou orales qui utilisent le langage comme matériau artistique. Elle ne se réduit pas à une liste de livres : c''est une manière de dire le monde, de raconter des histoires et de faire réfléchir.

## Trois approches complémentaires

- **L''approche formelle** étudie la langue, le style, la structure et les figures.
- **L''approche historique** replace l''œuvre dans son époque, ses courants et ses influences.
- **L''approche sociologique** interroge le rapport entre le texte, la société et le pouvoir.

## À quoi sert la littérature ?

Elle sert d''abord à **comprendre les autres** : en lisant, nous entrons dans des vies qui ne sont pas les nôtres. Elle sert aussi à **penser** : les grandes œuvres posent des questions morales, politiques et existentielles. Enfin, elle sert à **transmettre** : chaque génération reçoit un héritage et le transforme.

## Ce qu''il faut retenir

**La littérature n''est pas seulement un contenu à mémoriser.** C''est une pratique de lecture, d''interprétation et d''écriture. Pour analyser un texte, il faut observer ce qu''il dit, comment il le dit et à qui il s''adresse.'
WHERE `id_item` = 1;

UPDATE `course_items` SET
    `description` = 'Panorama des grands genres littéraires : roman, poésie, théâtre et essai, et de leurs codes.',
    `content` = '## Le roman

Le roman raconte une histoire, réelle ou inventée. Il repose sur des **personnages**, un **cadre**, une **intrigue** et un **narrateur**. Il peut être historique, policier, autobiographique, réaliste ou fantastique.

## La poésie

La poésie travaille le rythme, les sonorités et les images. Elle se reconnaît souvent à ses vers, à ses strophes et à ses jeux de sonorités, mais elle peut aussi être écrite en prose.

## Le théâtre

Le théâtre est un texte destiné à être joué. Il combine le **dialogue**, les **didascalies** (indications de jeu) et une **action dramatique**. La tragédie, la comédie et le drame en sont les formes principales.

## L''essai

L''essai expose une réflexion personnelle sur un sujet. Il argumente, critique, propose. Il se distingue du traité par sa liberté de ton et par la présence de l''auteur.

## Repères pratiques

- Identifier le genre dès la première lecture.
- Repérer les codes propres au genre (vers, didascalies, chapitres, thèse).
- Comparer deux genres pour mieux comprendre leurs frontières.'
WHERE `id_item` = 2;

UPDATE `course_items` SET
    `description` = 'Reconnaître et interpréter les principales figures de style dans un texte littéraire.',
    `content` = '## Qu''est-ce qu''une figure de style ?

Une figure de style est un procédé qui s''écarte de l''usage ordinaire du langage pour produire un effet. Elle ne décore pas seulement le texte : elle **transforme le sens** et guide la lecture.

## Les figures d''analogie

- **La comparaison** rapproche deux éléments avec un outil : « ses yeux sont bleus comme la mer ».
- **La métaphore** rapproche sans outil : « ses yeux sont une mer ».
- **La personnification** donne des traits humains à une chose : « la forêt murmure ».

## Les figures d''insistance

- **L''anaphore** répète un mot en début de phrase ou de vers.
- **L''hyperbole** exagère volontairement.
- **La gradation** ordonne les mots selon une intensité croissante.

## Les figures d''opposition

- **L''antithèse** oppose deux idées.
- **L''oxymore** réunit deux termes contradictoires : « une douce violence ».
- **Le chiasme** croise les éléments d''une structure.

## Méthode d''analyse

Ne vous contentez pas de nommer la figure. Expliquez **ce qu''elle évoque**, **pourquoi elle est employée ici** et **quel effet elle produit sur le lecteur**.'
WHERE `id_item` = 3;

UPDATE `course_items` SET
    `description` = 'Des classiques aux modernes : comprendre les grands mouvements qui traversent l''histoire littéraire.',
    `content` = '## Le classicisme

Au XVIIe siècle, le classicisme recherche l''ordre, la clarté et la mesure. Il s''appuie sur la raison et sur des règles précises, notamment au théâtre.

## Le romantisme

Au XIXe siècle, le romantisme donne la première place au **sentiment**, à l''imagination et à la nature. Il valorise l''individu, la liberté et l''expression du moi.

## Le réalisme et le naturalisme

Le réalisme veut représenter la société telle qu''elle est. Le naturalisme pousse cette démarche plus loin en cherchant des explications scientifiques aux comportements humains.

## Le symbolisme et le surréalisme

Le symbolisme cherche à suggérer plutôt qu''à décrire. Le surréalisme explore l''inconscient, le rêve et les associations libres.

## La négritude et les littératures africaines

La négritude affirme la dignité et la richesse des cultures noires. Elle ouvre la voie à une littérature africaine moderne, qui interroge la colonisation, l''identité et la mémoire.

## Ce qu''il faut retenir

Un courant littéraire n''est pas une étiquette figée. Il aide à situer une œuvre, à comprendre ses choix et à dialoguer avec d''autres textes.'
WHERE `id_item` = 4;

UPDATE `course_items` SET
    `description` = 'Contes, épopées, mythes et proverbes : les formes vivantes de la parole africaine.',
    `content` = '## Le conte

Le conte est un récit court, souvent merveilleux, transmis oralement. Il met en scène des personnages types et se termine souvent par une morale. Il peut être divertissant, éducatif ou initiatique.

## L''épopée

L''épopée raconte les exploits d''un héros et la destinée d''un peuple. Elle mêle histoire, légende et surnaturel. En Afrique de l''Ouest, les épopées sont encore aujourd''hui portées par des récits et des performances.

## Le mythe et la légende

Le mythe explique les origines du monde, des êtres et des institutions. La légende raconte un événement présenté comme vrai, souvent autour d''un lieu ou d''un personnage.

## Le proverbe et la devinette

Le proverbe concentre une sagesse en peu de mots. La devinette propose une énigme à résoudre : elle éduque l''esprit et renforce la complicité entre les participants.

## Pourquoi ces formes comptent-elles ?

L''oralité n''est pas une littérature « mineure ». Elle porte une **mémoire collective**, transmet des valeurs et invente des formes poétiques. L''étudier, c''est apprendre à écouter la parole vivante.'
WHERE `id_item` = 5;

UPDATE `course_items` SET
    `description` = 'La performance orale : voix, gestes, rythme et participation du public.',
    `content` = '## Le conteur est un acteur

Le conteur ne récite pas un texte : il le **joue**. Sa présence, son regard et son énergie créent une relation directe avec l''auditoire.

## La voix et le rythme

La voix se module : elle accélère, ralentit, monte, chuchote. Les pauses servent à créer le suspense et à laisser le public réagir. Le rythme donne corps au récit.

## Le geste et l''espace

Le conteur utilise ses mains, son corps et l''espace autour de lui. Un geste peut remplacer une longue description. Le déplacement dans l''espace marque les changements de scène.

## Le public participe

Dans de nombreuses traditions, le public répond, reprend les refrains, pose des questions. Le conte devient alors une **création collective**.

## Transmettre

L''art du conteur se transmet par l''écoute et la pratique. Il demande de la mémoire, de l''improvisation et, surtout, du plaisir à raconter.'
WHERE `id_item` = 6;

UPDATE `course_items` SET
    `description` = 'La sagesse condensée : structure, fonctions et usage social des proverbes et des devinettes.',
    `content` = '## Le proverbe

Le proverbe est une phrase courte et imagée qui exprime une vérité d''expérience. Il est anonyme, stable et souvent métaphorique.

## Sa structure

Un proverbe repose fréquemment sur :

- une **image concrète** ;
- un **parallélisme** ou une **opposition** ;
- une **formule brève** facile à retenir.

## Ses fonctions

- **Éduquer** : transmettre des valeurs.
- **Argumenter** : donner du poids à une parole.
- **Rassembler** : rappeler une expérience commune.

## La devinette

La devinette propose une énigme dont la réponse est connue de celui qui la pose. Elle stimule l''observation, la logique et l''imagination. Elle crée aussi un jeu social.

## À vous de jouer

Choisissez un proverbe de votre région, expliquez son image et montrez dans quelle situation précise on peut l''employer.'
WHERE `id_item` = 7;

UPDATE `course_items` SET
    `description` = 'Berceuses, chants de travail et chants rituels : la mémoire d''un peuple en musique.',
    `content` = '## Le chant comme mémoire

Le chant accompagne la vie quotidienne : naissance, travail, mariage, deuil, fête. Il conserve des paroles, des langues et des savoirs qui parfois ne s''écrivent pas.

## Les grands types de chants

- **Les berceuses** apaisent l''enfant et transmettent l''affection.
- **Les chants de travail** rythment l''effort collectif.
- **Les chants rituels** accompagnent les cérémonies et les passages de la vie.
- **Les chants d''amour et de satire** expriment les émotions et les critiques sociales.

## Poétique du chant

Le chant associe le texte, la mélodie et le rythme. Les répétitions et les refrains facilitent la mémorisation et la participation de tous.

## Le chant aujourd''hui

Les chants traditionnels se transforment : ils rencontrent la radio, la scène et les nouveaux instruments. Les étudier permet de comprendre comment une culture reste vivante.'
WHERE `id_item` = 8;

UPDATE `course_items` SET
    `description` = 'Les outils d''analyse du récit : narrateur, focalisation, temps et structure.',
    `content` = '## Qu''est-ce que la narratologie ?

La narratologie étudie la manière dont un récit est raconté : qui parle, qui voit, à quel rythme et dans quel ordre.

## Le narrateur

Le narrateur n''est pas l''auteur. Il peut être **intérieur** à l''histoire ou **extérieur**. Il peut être omniscient, c''est-à-dire tout savoir des personnages.

## La focalisation

- **Focalisation zéro** : le narrateur sait tout.
- **Focalisation interne** : l''histoire est perçue à travers un personnage.
- **Focalisation externe** : le narrateur observe sans entrer dans les pensées.

## Le temps du récit

On distingue le temps de l''histoire et le temps du récit. Les **analepses** reviennent en arrière, les **prolepses** anticipent. Les accélérations et les ralentissements organisent le rythme.

## La structure narrative

Repérez la situation initiale, l''élément perturbateur, les péripéties, le dénouement et la situation finale. Cette grille aide à comprendre la logique du récit.'
WHERE `id_item` = 9;

UPDATE `course_items` SET
    `description` = 'Étudier un texte comme un acte de communication : énonciation, registres et argumentation.',
    `content` = '## Le discours

Un discours est un texte produit par quelqu''un, pour quelqu''un, dans une situation donnée. L''analyse du discours s''intéresse donc à la fois au texte et à son contexte.

## L''énonciation

L''énonciation est l''acte de produire un énoncé. On repère :

- les **pronoms** (je, tu, nous) ;
- les **marques de temps** ;
- les **modalisations** (peut-être, certainement, hélas).

## Les registres

Le registre peut être lyrique, épique, pathétique, comique, satirique ou didactique. Il traduit une intention et une émotion.

## L''argumentation

Un texte argumentatif défend une thèse avec des arguments et des exemples. Il peut convaincre par la raison ou persuader par l''émotion.

## Méthode

Pour analyser un discours, demandez-vous : **qui parle, à qui, pourquoi, avec quels moyens ?**'
WHERE `id_item` = 10;

UPDATE `course_items` SET
    `description` = 'Lire le texte dans son contexte : rapports entre littérature, société et pouvoir.',
    `content` = '## Origines

La sociocritique naît de l''idée que la littérature ne flotte pas au-dessus de la société. Elle est traversée par des valeurs, des conflits et des représentations collectives.

## Principes

La sociocritique étudie :

- le **contexte de production** de l''œuvre ;
- les **représentations sociales** qu''elle véhicule ;
- les **rapports de pouvoir** qu''elle révèle ou conteste.

## Le texte et son contexte

Une œuvre peut confirmer l''ordre social, le critiquer ou le réinventer. Elle n''est jamais un simple reflet : elle propose une lecture du monde.

## Applications

On peut analyser la représentation de la paysannerie, de la ville, de la colonisation, de la famille ou du travail. On compare ensuite cette représentation avec les discours de l''époque.

## Limites

La sociocritique ne doit pas réduire l''œuvre à son contexte. Le texte garde une **forme**, un **style** et une **singularité** qu''il faut aussi respecter.'
WHERE `id_item` = 11;

UPDATE `course_items` SET
    `description` = 'Commentaire composé, dissertation et fiche de lecture : méthodes pas à pas.',
    `content` = '## Trois exercices, trois objectifs

- Le **commentaire composé** analyse un texte précis.
- La **dissertation** répond à une question en construisant un raisonnement.
- La **fiche de lecture** synthétise une œuvre pour soi-même.

## Le commentaire composé

1. Lire le texte plusieurs fois et noter ses impressions.
2. Repérer les procédés : structure, figures, temps, voix.
3. Formuler deux ou trois axes de lecture.
4. Rédiger une introduction, un développement organisé et une conclusion.

## La dissertation

1. Analyser le sujet et définir ses termes.
2. Formuler une problématique.
3. Construire un plan en deux ou trois parties.
4. Illustrer chaque argument par un exemple littéraire précis.

## La fiche de lecture

Notez l''auteur, le titre, la date, le genre, les personnages, l''intrigue, les thèmes et deux ou trois citations importantes.

## Conseils

Travaillez toujours du **texte vers l''idée**, jamais l''inverse. Une analyse juste s''appuie sur des citations précises et sur une interprétation personnelle.'
WHERE `id_item` = 12;
