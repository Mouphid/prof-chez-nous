<?php
/**
 * Reusable animated card component.
 * Usage: <?php include "includes/animated-card.php"; ?>
 *
 * Variables (all optional):
 *   @param string $card_title       Card title
 *   @param string $card_text        Card description
 *   @param string $card_icon        Phosphor icon class (e.g. 'ph-book-open')
 *   @param string $card_icon_bg     Background color class (e.g. 'bg-indigo-100')
 *   @param string $card_icon_color  Icon color class (e.g. 'text-primary')
 *   @param string $card_image       Optional image URL
 *   @param string $card_link        Optional URL to make card clickable
 *   @param string $card_badge       Optional badge text
 *   @param string $card_badge_color Badge color class (e.g. 'bg-primary text-white')
 *   @param string $card_animation   Animation class (default: 'animate-slideUp')
 *   @param string $card_class       Extra classes on the card wrapper
 *   @param array  $card_stats       Array of [icon, label, value] for stats footer
 */
$card_title       = $card_title ?? 'Titre de la carte';
$card_text        = $card_text ?? '';
$card_icon        = $card_icon ?? '';
$card_icon_bg     = $card_icon_bg ?? 'bg-indigo-100';
$card_icon_color  = $card_icon_color ?? 'text-primary';
$card_image       = $card_image ?? '';
$card_link        = $card_link ?? '';
$card_badge       = $card_badge ?? '';
$card_badge_color = $card_badge_color ?? 'bg-primary text-white';
$card_animation   = $card_animation ?? 'animate-slideUp';
$card_class       = $card_class ?? '';
$card_stats       = $card_stats ?? [];
$tag = $card_link ? 'a' : 'div';
$attrs = $tag === 'a' ? 'href="' . htmlspecialchars($card_link) . '"' : '';
?>
<<?= $tag ?> <?= $attrs ?> class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-lg transition-all group <?= $card_animation ?> <?= $card_class ?> <?= $tag === 'a' ? 'hover:border-primary/20 hover:-translate-y-0.5 block' : '' ?>">
    <?php if ($card_image): ?>
    <div class="overflow-hidden rounded-t-xl">
        <img src="<?= $card_image ?>" alt="<?= htmlspecialchars($card_title) ?>" loading="lazy" class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500">
    </div>
    <?php endif; ?>
    <div class="p-5 <?= $card_image ? '' : 'pt-5' ?>">
        <?php if ($card_badge): ?>
        <span class="inline-block text-xs font-medium <?= $card_badge_color ?> px-2 py-0.5 rounded-full mb-3"><?= $card_badge ?></span>
        <?php endif; ?>
        <?php if ($card_icon): ?>
        <div class="w-11 h-11 <?= $card_icon_bg ?> <?= $card_icon_color ?> rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <i class="<?= $card_icon ?> text-xl"></i>
        </div>
        <?php endif; ?>
        <h3 class="text-base font-semibold text-gray-900 mb-1.5"><?= $card_title ?></h3>
        <?php if ($card_text): ?>
        <p class="text-sm text-gray-500 leading-relaxed"><?= $card_text ?></p>
        <?php endif; ?>
        <?php if (!empty($card_stats)): ?>
        <div class="flex items-center gap-3 pt-3 mt-3 border-t border-gray-50">
            <?php foreach ($card_stats as $stat): ?>
            <span class="inline-flex items-center gap-1 text-xs text-gray-400"><i class="<?= $stat[0] ?>"></i> <?= $stat[1] ?> <?= $stat[2] ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</<?= $tag ?>>
