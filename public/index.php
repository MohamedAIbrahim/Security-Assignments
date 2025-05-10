<?php
require_once '../includes/header.php';
?>

<div class="hero">
    <div class="hero-content">
        <h1>Welcome to Vulnerable Bank</h1>
        <p class="subtitle">A platform for learning web security</p>
        <div class="cta-buttons">
            <a href="login.php" class="btn btn-primary">Get Started</a>
            <a href="#about" class="btn btn-secondary">Learn More</a>
        </div>
    </div>
</div>

<div class="features" id="about">
    <div class="feature-card">
        <h3>Educational Platform</h3>
        <p>Learn about web security vulnerabilities and their prevention in a safe environment.</p>
    </div>
    <div class="feature-card">
        <h3>Hands-on Experience</h3>
        <p>Practice identifying and understanding common security vulnerabilities.</p>
    </div>
    <div class="feature-card">
        <h3>Safe Environment</h3>
        <p>Test and learn in a controlled environment designed for educational purposes.</p>
    </div>
</div>

<style>
    .hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 6rem 2rem;
        text-align: center;
        margin: -2rem -2rem 2rem -2rem;
    }

    .hero-content {
        max-width: 800px;
        margin: 0 auto;
    }

    .hero h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .subtitle {
        font-size: 1.25rem;
        opacity: 0.9;
        margin-bottom: 2rem;
    }

    .cta-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background-color: white;
        color: var(--primary);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .btn-secondary {
        background-color: transparent;
        color: white;
        border: 2px solid white;
    }

    .btn-secondary:hover {
        background-color: rgba(255,255,255,0.1);
        transform: translateY(-2px);
    }

    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        padding: 2rem 0;
    }

    .feature-card {
        background: var(--surface);
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }

    .feature-card:hover {
        transform: translateY(-4px);
    }

    .feature-card h3 {
        color: var(--primary);
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .feature-card p {
        color: var(--text-light);
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .hero {
            padding: 4rem 1rem;
        }

        .hero h1 {
            font-size: 2rem;
        }

        .cta-buttons {
            flex-direction: column;
        }

        .features {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?> 