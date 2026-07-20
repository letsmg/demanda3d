import { ref } from 'vue';

export function useShareDialog() {
    const shareDialogOpen = ref(false);
    const shareUrl = ref('');
    const shareTitle = ref('');
    const shareCopied = ref(false);

    function openShare(productName: string, productSlug?: string): void {
        shareUrl.value = productSlug
            ? `${window.location.origin}/store/${productSlug}`
            : window.location.href;
        shareTitle.value = productName;
        shareDialogOpen.value = true;
        shareCopied.value = false;
    }

    function copyLink(): void {
        navigator.clipboard.writeText(shareUrl.value).then(() => {
            shareCopied.value = true;
            setTimeout(() => {
                shareCopied.value = false;
            }, 2000);
        });
    }

    function shareWhatsApp(): void {
        const text = encodeURIComponent(
            `${shareTitle.value} — ${shareUrl.value}`,
        );
        const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        const url = isMobile
            ? `whatsapp://send?text=${text}`
            : `https://wa.me/?text=${text}`;

        window.open(url, '_blank');
    }

    function shareFacebook(): void {
        const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`;

        window.open(url, '_blank');
    }

    function shareTwitter(): void {
        const text = encodeURIComponent(shareTitle.value);
        const url = `https://x.com/intent/post?text=${text}&url=${encodeURIComponent(shareUrl.value)}`;

        window.open(url, '_blank');
    }

    function shareTelegram(): void {
        const text = encodeURIComponent(
            `${shareTitle.value} — ${shareUrl.value}`,
        );
        const url = `https://t.me/share/url?url=${encodeURIComponent(shareUrl.value)}&text=${text}`;

        window.open(url, '_blank');
    }

    return {
        shareDialogOpen,
        shareUrl,
        shareTitle,
        shareCopied,
        openShare,
        copyLink,
        shareWhatsApp,
        shareFacebook,
        shareTwitter,
        shareTelegram,
    };
}
