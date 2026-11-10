<style>
    .chatbot-button {
        position: fixed !important;
        right: 1.5rem !important;
        bottom: 1.5rem !important;
        z-index: 1100;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 4.5rem;
        height: 4.5rem;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: linear-gradient(145deg, #0077b6, #003f5c);
        color: #fff;
        box-shadow: 0 0.35rem 1rem rgba(0, 40, 60, 0.35);
        font-size: 2rem;
        cursor: pointer;
        transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .chatbot-button:hover {
        box-shadow: 0 0.5rem 1.25rem rgba(0, 40, 60, 0.45);
        transform: translateY(-3px);
    }

    .chatbot-button:focus-visible {
        outline: 3px solid #efaf49;
        outline-offset: 4px;
    }

    @media (max-width: 575.98px) {
        .chatbot-button {
            right: 1rem !important;
            bottom: 1rem !important;
            width: 4rem;
            height: 4rem;
        }
    }
</style>

<button
    type="button"
    class="chatbot-button allow-rounded"
    aria-label="AI assistant (coming soon)"
    title="AI assistant (coming soon)"
>
    <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
</button>
