<section
    class="product-questions"
    data-product-community
>
    <div class="product-questions__header">
        <div>
            <p class="home-section__eyebrow">
                Cộng đồng sản phẩm
            </p>

            <h2>Hỏi đáp và thảo luận</h2>

            <p>
                Mọi thành viên có thể đặt câu hỏi và cùng nhau trả lời.
            </p>
        </div>

        <strong data-community-count>
            {{ number_format($questionCount) }} nội dung
        </strong>
    </div>

    <div
        class="product-questions__alert"
        data-community-message
        hidden
    ></div>

    <div class="product-questions__layout">
        <aside class="product-question-form-card">
            <div class="product-question-form-card__heading">
                <span>?</span>

                <div>
                    <h3>Tham gia thảo luận</h3>

                    <p>
                        Nội dung sẽ được đăng công khai ngay lập tức.
                    </p>
                </div>
            </div>

            <form
                action="{{ route(
                    'products.questions.store',
                    $product->id
                ) }}"
                method="POST"
                class="product-question-form"
                data-community-question-form
            >
                @csrf

                @guest
                    <div class="product-question-form__grid">
                        <div class="product-question-form__field">
                            <label for="community-name">
                                Tên của bạn
                            </label>

                            <input
                                id="community-name"
                                type="text"
                                name="guest_name"
                                maxlength="150"
                                required
                            >
                        </div>

                        <div class="product-question-form__field">
                            <label for="community-email">
                                Email
                            </label>

                            <input
                                id="community-email"
                                type="email"
                                name="guest_email"
                                maxlength="255"
                                required
                            >
                        </div>
                    </div>
                @else
                    @php
                        $currentUserIsShop = auth()
                            ->user()
                            ->roles
                            ?->contains(
                                fn ($role) => in_array(
                                    $role->name,
                                    ['admin', 'super_admin'],
                                    true
                                )
                            );
                    @endphp

                    <div class="product-question-form__user">
                        <div class="product-question-form__avatar">
                            <span>
                                {{ mb_strtoupper(
                                    mb_substr(
                                        $currentUserIsShop
                                            ? site_name()
                                            : auth()->user()->name,
                                        0,
                                        1
                                    )
                                ) }}
                            </span>
                        </div>

                        <div>
                            @if ($currentUserIsShop)
                                <span class="community-shop-badge">
                                    Chủ shop
                                </span>
                            @endif

                            <strong>
                                {{ $currentUserIsShop
                                    ? site_name()
                                    : auth()->user()->name }}
                            </strong>

                            <small>
                                Đang đăng bằng tài khoản hiện tại
                            </small>
                        </div>
                    </div>
                @endguest

                <div class="product-question-form__field">
                    <label for="community-question">
                        Nội dung
                    </label>

                    <textarea
                        id="community-question"
                        name="question"
                        maxlength="2000"
                        placeholder="Viết câu hỏi, chia sẻ kinh nghiệm hoặc trao đổi về sản phẩm..."
                        required
                    ></textarea>
                </div>

                <button
                    type="submit"
                    class="product-question-form__submit"
                >
                    Đăng nội dung
                </button>

                <p class="product-question-form__notice">
                    Nội dung hiển thị ngay, không cần chờ duyệt.
                </p>
            </form>
        </aside>

        <div
            class="product-question-list"
            data-community-list
        >
            @forelse ($productQuestions as $question)
                @php
                    $questionIsShop =
                        $question->user
                        && $question->user->roles
                            ?->contains(
                                fn ($role) => in_array(
                                    $role->name,
                                    ['admin', 'super_admin'],
                                    true
                                )
                            );

                    $questionAuthor = $questionIsShop
                        ? site_name()
                        : (
                            $question->user?->name
                            ?? $question->guest_name
                            ?? 'Khách hàng'
                        );
                @endphp

                <article
                    class="product-question-item"
                    data-community-question="{{ $question->id }}"
                >
                    <div class="product-question-item__question">
                        <div class="product-question-item__avatar">
                            <span>
                                {{ mb_strtoupper(
                                    mb_substr(
                                        $questionAuthor,
                                        0,
                                        1
                                    )
                                ) }}
                            </span>
                        </div>

                        <div class="product-question-item__content">
                            <div class="product-question-item__meta">
                                <div class="product-question-item__author">
                                    @if ($questionIsShop)
                                        <span class="community-shop-badge">
                                            Chủ shop
                                        </span>
                                    @endif

                                    <strong>
                                        {{ $questionAuthor }}
                                    </strong>
                                </div>

                                <time>
                                    {{ $question->created_at
                                        ->format('d/m/Y H:i') }}
                                </time>
                            </div>

                            <p>
                                {!! nl2br(e($question->question)) !!}
                            </p>
                        </div>
                    </div>

                    <div
                        class="product-question-answers"
                        data-community-answers
                    >
                        @foreach (
                            $question->community_answers ?? []
                            as $answer
                        )
                            <div
                                class="product-question-answer
                                    {{ $answer->is_shop
                                        ? 'is-shop'
                                        : '' }}"
                            >
                                <div class="product-question-answer__heading">
                                    @if ($answer->is_shop)
                                        <span class="community-shop-badge">
                                            Chủ shop
                                        </span>
                                    @endif

                                    <strong>
                                        {{ $answer->author_name }}
                                    </strong>
                                </div>

                                <p>
                                    {!! nl2br(e($answer->answer)) !!}
                                </p>

                                <time>
                                    {{ $answer->created_at_display }}
                                </time>
                            </div>
                        @endforeach
                    </div>

                    @auth
                        <form
                            action="{{ route(
                                'products.questions.reply',
                                $question
                            ) }}"
                            method="POST"
                            class="community-reply-form"
                            data-community-reply-form
                        >
                            @csrf

                            <textarea
                                name="answer"
                                rows="2"
                                maxlength="3000"
                                placeholder="Viết câu trả lời..."
                                required
                            ></textarea>

                            <button type="submit">
                                Trả lời
                            </button>
                        </form>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="community-login-reply"
                        >
                            Đăng nhập để trả lời
                        </a>
                    @endauth
                </article>
            @empty
                <div
                    class="product-question-empty"
                    data-community-empty
                >
                    <span>?</span>
                    <h3>Chưa có nội dung thảo luận</h3>
                    <p>
                        Hãy là người đầu tiên đặt câu hỏi hoặc chia sẻ.
                    </p>
                </div>
            @endforelse

            @if ($productQuestions->hasPages())
                <div class="product-question-pagination">
                    {{ $productQuestions
                        ->withQueryString()
                        ->links() }}
                </div>
            @endif
        </div>
    </div>
</section>

<style>
    .community-shop-badge {
        display: inline-flex;
        align-items: center;
        padding: .24rem .55rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #be185d, #ec4899);
        color: #fff !important;
        font-size: .66rem !important;
        font-weight: 900 !important;
    }

    .community-reply-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .65rem;
        margin: 1rem 0 0 3.65rem;
    }

    .community-reply-form textarea {
        width: 100%;
        box-sizing: border-box;
        padding: .75rem .85rem;
        border: 1px solid #d1d5db;
        border-radius: .75rem;
        resize: vertical;
    }

    .community-reply-form button {
        align-self: end;
        min-height: 42px;
        padding: .65rem 1rem;
        border: 0;
        border-radius: .7rem;
        background: #db2777;
        color: #fff;
        font-weight: 800;
        cursor: pointer;
    }

    .product-question-answer.is-shop {
        border-left-color: #be185d;
        background: linear-gradient(135deg, #fff1f7, #fff);
    }

    .community-login-reply {
        display: inline-flex;
        margin: .9rem 0 0 3.65rem;
        color: #db2777;
        font-size: .8rem;
        font-weight: 800;
        text-decoration: none;
    }

    @media (max-width: 580px) {
        .community-reply-form {
            grid-template-columns: 1fr;
            margin-left: 0;
        }

        .community-login-reply {
            margin-left: 0;
        }
    }
</style>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const root = document.querySelector(
                '[data-product-community]'
            );

            if (! root) {
                return;
            }

            const list = root.querySelector(
                '[data-community-list]'
            );

            const message = root.querySelector(
                '[data-community-message]'
            );

            const count = root.querySelector(
                '[data-community-count]'
            );

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

            const showMessage = function (
                text,
                type = 'success'
            ) {
                if (! message) {
                    return;
                }

                message.hidden = false;
                message.textContent = text;
                message.className =
                    'product-questions__alert '
                    + (
                        type === 'error'
                            ? 'is-error'
                            : 'is-success'
                    );

                window.setTimeout(function () {
                    message.hidden = true;
                }, 3000);
            };

            const createBadge = function () {
                const badge =
                    document.createElement('span');

                badge.className =
                    'community-shop-badge';

                badge.textContent = 'Chủ shop';

                return badge;
            };

            const createAnswer = function (answer) {
                const wrapper =
                    document.createElement('div');

                wrapper.className =
                    'product-question-answer'
                    + (
                        answer.is_shop
                            ? ' is-shop'
                            : ''
                    );

                const heading =
                    document.createElement('div');

                heading.className =
                    'product-question-answer__heading';

                if (answer.is_shop) {
                    heading.appendChild(createBadge());
                }

                const strong =
                    document.createElement('strong');

                strong.textContent =
                    answer.author_name;

                heading.appendChild(strong);

                const paragraph =
                    document.createElement('p');

                paragraph.textContent = answer.answer;

                const time =
                    document.createElement('time');

                time.textContent = answer.created_at;

                wrapper.append(
                    heading,
                    paragraph,
                    time
                );

                return wrapper;
            };

            const bindReplyForm = function (form) {
                form.addEventListener(
                    'submit',
                    async function (event) {
                        event.preventDefault();

                        const button =
                            form.querySelector('button');

                        const textarea =
                            form.querySelector('textarea');

                        const original =
                            button.textContent;

                        button.disabled = true;
                        button.textContent =
                            'Đang gửi...';

                        try {
                            const response = await fetch(
                                form.action,
                                {
                                    method: 'POST',
                                    headers: {
                                        Accept:
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            csrfToken,

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },
                                    body:
                                        new FormData(form),

                                    credentials:
                                        'same-origin',
                                }
                            );

                            const data =
                                await response.json();

                            if (
                                ! response.ok
                                || ! data.success
                            ) {
                                throw new Error(
                                    data.message
                                    || 'Không thể gửi câu trả lời.'
                                );
                            }

                            const article = form.closest(
                                '[data-community-question]'
                            );

                            article
                                .querySelector(
                                    '[data-community-answers]'
                                )
                                .appendChild(
                                    createAnswer(
                                        data.answer
                                    )
                                );

                            textarea.value = '';

                            showMessage(data.message);
                        } catch (error) {
                            showMessage(
                                error.message,
                                'error'
                            );
                        } finally {
                            button.disabled = false;
                            button.textContent =
                                original;
                        }
                    }
                );
            };

            root
                .querySelectorAll(
                    '[data-community-reply-form]'
                )
                .forEach(bindReplyForm);

            const questionForm = root.querySelector(
                '[data-community-question-form]'
            );

            questionForm?.addEventListener(
                'submit',
                async function (event) {
                    event.preventDefault();

                    const button =
                        questionForm.querySelector(
                            'button[type="submit"]'
                        );

                    const original =
                        button.textContent;

                    button.disabled = true;
                    button.textContent =
                        'Đang đăng...';

                    try {
                        const response = await fetch(
                            questionForm.action,
                            {
                                method: 'POST',
                                headers: {
                                    Accept:
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        csrfToken,

                                    'X-Requested-With':
                                        'XMLHttpRequest',
                                },
                                body:
                                    new FormData(
                                        questionForm
                                    ),
                                credentials:
                                    'same-origin',
                            }
                        );

                        const data =
                            await response.json();

                        if (
                            ! response.ok
                            || ! data.success
                        ) {
                            const firstError =
                                data.errors
                                    ? Object.values(
                                        data.errors
                                    )[0]?.[0]
                                    : null;

                            throw new Error(
                                firstError
                                || data.message
                                || 'Không thể đăng nội dung.'
                            );
                        }

                        root
                            .querySelector(
                                '[data-community-empty]'
                            )
                            ?.remove();

                        const question =
                            data.question;

                        const article =
                            document.createElement(
                                'article'
                            );

                        article.className =
                            'product-question-item';

                        article.dataset.communityQuestion =
                            question.id;

                        article.innerHTML = `
                            <div class="product-question-item__question">
                                <div class="product-question-item__avatar">
                                    <span>${question.author_name.charAt(0).toUpperCase()}</span>
                                </div>

                                <div class="product-question-item__content">
                                    <div class="product-question-item__meta">
                                        <div class="product-question-item__author"></div>
                                        <time>${question.created_at}</time>
                                    </div>

                                    <p></p>
                                </div>
                            </div>

                            <div
                                class="product-question-answers"
                                data-community-answers
                            ></div>
                        `;

                        const author = article.querySelector(
                            '.product-question-item__author'
                        );

                        if (question.is_shop) {
                            author.appendChild(
                                createBadge()
                            );
                        }

                        const strong =
                            document.createElement(
                                'strong'
                            );

                        strong.textContent =
                            question.author_name;

                        author.appendChild(strong);

                        article.querySelector('p')
                            .textContent =
                                question.question;

                        @auth
                            const replyForm =
                                document.createElement(
                                    'form'
                                );

                            replyForm.action =
                                question.reply_url;

                            replyForm.method = 'POST';
                            replyForm.className =
                                'community-reply-form';

                            replyForm.dataset
                                .communityReplyForm = '';

                            replyForm.innerHTML = `
                                <textarea
                                    name="answer"
                                    rows="2"
                                    maxlength="3000"
                                    placeholder="Viết câu trả lời..."
                                    required
                                ></textarea>

                                <button type="submit">
                                    Trả lời
                                </button>
                            `;

                            bindReplyForm(replyForm);
                            article.appendChild(replyForm);
                        @endauth

                        list.prepend(article);

                        questionForm.reset();

                        if (count) {
                            const current =
                                parseInt(
                                    count.textContent,
                                    10
                                ) || 0;

                            count.textContent =
                                `${current + 1} nội dung`;
                        }

                        showMessage(data.message);
                    } catch (error) {
                        showMessage(
                            error.message,
                            'error'
                        );
                    } finally {
                        button.disabled = false;
                        button.textContent =
                            original;
                    }
                }
            );
        }
    );
</script>
