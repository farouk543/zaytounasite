@extends('layouts.app')

@section('title', 'Club Islamique - Session Ete')

@section('content')
<style>
    .islamic-club-page .summer-club-hero {
        background:
            radial-gradient(circle at 82% 24%, rgba(212,176,86,.22), transparent 28%),
            radial-gradient(circle at 18% 18%, rgba(15,118,86,.18), transparent 30%),
            linear-gradient(135deg, #052e1f 0%, #08251b 56%, #fbf6e8 56%, #fbf6e8 100%);
    }

    .islamic-club-page .summer-club-visual {
        border-color: rgba(212,176,86,.34);
    }

    .islamic-club-list {
        display: grid;
        gap: 14px;
        margin-top: 24px;
    }

    .islamic-club-listItem {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 16px 18px;
        border-radius: 20px;
        background: #fff;
        border: 1px solid rgba(6,51,34,.08);
        box-shadow: 0 16px 42px rgba(8,38,24,.06);
        color: #10231d;
        font-weight: 700;
        line-height: 1.55;
    }

    .islamic-club-listItem::before {
        content: "";
        width: 10px;
        height: 10px;
        margin-top: 7px;
        flex: 0 0 10px;
        border-radius: 999px;
        background: linear-gradient(135deg, #d4b056, #0b3b2e);
    }

    .islamic-club-priceGrid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
        max-width: 920px;
        margin: 0 auto;
    }

    @media (min-width: 760px) {
        .islamic-club-priceGrid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .islamic-club-priceCard {
        position: relative;
        overflow: hidden;
        min-height: 100%;
    }

    .islamic-club-priceCard::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(135deg, rgba(212,176,86,.16), transparent 42%);
    }

    .islamic-club-price {
        position: relative;
        margin: 18px 0 12px;
        color: #063322;
        font-size: clamp(2rem, 4vw, 3rem);
        line-height: 1;
        font-weight: 900;
    }

    .islamic-club-price span {
        font-size: 1rem;
        color: #b8912d;
    }

    .islamic-club-contactBox {
        margin-top: 26px;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        color: rgba(255,255,255,.82);
        font-weight: 700;
    }

    .islamic-club-contactBox span {
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(255,255,255,.08);
    }
</style>

<div class="summer-club-page islamic-club-page">
    <section class="summer-club-hero">
        <div class="za-container">
            <div class="summer-club-heroGrid">
                <div>
                    <span class="summer-club-kicker">Session Ete</span>
                    <h1 class="summer-club-title">Club Islamique - Session Ete</h1>
                    <p class="summer-club-subtitle">
                        Arabe, Coran et education islamique pour un ete utile, enrichissant et inoubliable.
                    </p>

                    <div class="summer-club-badges" aria-label="Themes du programme">
                        <span class="summer-club-chip">Session Ete</span>
                        <span class="summer-club-chip">Arabe &amp; Coran</span>
                    </div>

                    <p class="summer-club-subtitle">
                        Offrez a votre enfant un ete benefique avec l'apprentissage de l'Arabe, du Coran et des valeurs islamiques dans un cadre bienveillant et structure.
                    </p>

                    <div class="summer-club-actions">
                        <a class="summer-club-actionPrimary" href="https://wa.me/21657029460">S'inscrire maintenant</a>
                        <a class="summer-club-actionSecondary" href="#formules">Voir les formules</a>
                    </div>
                </div>

                <div class="summer-club-visual">
                    <img src="{{ asset('images/clubs/club-islamique-session-ete.jpeg') }}" alt="Club Islamique Session Ete Arabe et Coran">
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-section">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">Programme</span>
                <h2 class="summer-club-heading">Presentation du programme</h2>
                <p class="summer-club-lead">
                    Un accompagnement en ligne, clair et structure, pour progresser dans l'Arabe, le Coran et les valeurs islamiques.
                </p>
            </div>

            <div class="summer-club-grid3">
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Cours en ligne en direct</h3>
                    <p class="summer-club-cardText">Des seances interactives pour garder le lien avec l'enseignant et suivre la progression de l'enfant.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Apprentissage de l'Arabe</h3>
                    <p class="summer-club-cardText">Lecture, bases de langue et pratique adaptees au niveau de chaque enfant.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Apprentissage du Coran</h3>
                    <p class="summer-club-cardText">Memorisation, recitation et correction selon le rythme et les objectifs de l'eleve.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Education islamique</h3>
                    <p class="summer-club-cardText">Des notions simples, positives et adaptees aux enfants pour comprendre les valeurs islamiques.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Suivi personnalise</h3>
                    <p class="summer-club-cardText">Une attention portee a la motivation, aux difficultes et a la progression de chaque enfant.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-section summer-club-soft">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">Apprentissages</span>
                <h2 class="summer-club-heading">Ce que l'enfant va apprendre</h2>
            </div>

            <div class="islamic-club-list">
                <div class="islamic-club-listItem">Lecture et bases de la langue arabe</div>
                <div class="islamic-club-listItem">Memorisation et recitation du Coran selon le niveau</div>
                <div class="islamic-club-listItem">Comprehension simple des valeurs islamiques</div>
                <div class="islamic-club-listItem">Discipline, respect et motivation</div>
                <div class="islamic-club-listItem">Progression adaptee a chaque enfant</div>
            </div>
        </div>
    </section>

    <section class="summer-club-section" id="formules">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">Tarifs</span>
                <h2 class="summer-club-heading">Formules proposees</h2>
            </div>

            <div class="islamic-club-priceGrid">
                <div class="summer-club-card islamic-club-priceCard">
                    <h3 class="summer-club-cardTitle">En petit groupe</h3>
                    <div class="islamic-club-price">208 <span>CAD / session</span></div>
                    <p class="summer-club-cardText">Apprentissage collectif, ambiance positive, progression encadree.</p>
                    <a class="summer-club-cardButton" href="https://wa.me/21657029460">Choisir cette formule</a>
                </div>

                <div class="summer-club-card islamic-club-priceCard">
                    <h3 class="summer-club-cardTitle">Cours individuel</h3>
                    <div class="islamic-club-price">324 <span>CAD / session</span></div>
                    <p class="summer-club-cardText">Attention personnalisee, objectifs adaptes, suivi plus precis.</p>
                    <a class="summer-club-cardButton" href="https://wa.me/21657029460">Choisir cette formule</a>
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-section summer-club-soft">
        <div class="za-container">
            <div class="summer-club-head">
                <span class="summer-club-eyebrow">Avantages</span>
                <h2 class="summer-club-heading">Un cadre serieux, flexible et motivant</h2>
            </div>

            <div class="summer-club-grid3">
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Enseignants qualifies</h3>
                    <p class="summer-club-cardText">Des enseignants qualifies et experimentes pour accompagner les enfants avec bienveillance.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Programme structure</h3>
                    <p class="summer-club-cardText">Une progression claire, adaptee et facile a suivre pendant toute la session.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Seances flexibles</h3>
                    <p class="summer-club-cardText">Un format en ligne pratique pour organiser l'apprentissage pendant l'ete.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Suivi personnalise</h3>
                    <p class="summer-club-cardText">Des retours utiles pour aider chaque enfant a progresser avec confiance.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Partout au Canada</h3>
                    <p class="summer-club-cardText">Un programme accessible aux familles ou qu'elles soient au Canada.</p>
                </div>
                <div class="summer-club-card">
                    <h3 class="summer-club-cardTitle">Ambiance positive</h3>
                    <p class="summer-club-cardText">Un environnement motivant qui encourage l'effort, le respect et la regularite.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="summer-club-cta">
        <div class="za-container">
            <div class="summer-club-ctaBox">
                <h2 class="summer-club-ctaTitle">Inscrire votre enfant au Club Islamique</h2>
                <p class="summer-club-ctaText">
                    Contactez l'equipe Zaytouna Academy pour choisir la formule adaptee et finaliser l'inscription.
                </p>
                <a class="summer-club-actionPrimary" href="https://wa.me/21657029460">S'inscrire maintenant</a>

                <div class="islamic-club-contactBox">
                    <span>+216 57 029 460</span>
                    <span>+216 57 029 461</span>
                    <span>www.zaytouna-academy.net</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
