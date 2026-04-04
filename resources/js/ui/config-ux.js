import { initOnce } from '../core/once';
import Swal from 'sweetalert2';

function isVisible(el) {
    return !!(el && (el.offsetParent !== null || el.getClientRects().length > 0));
}

function firstFocusableField(scope) {
    if (!scope || !scope.querySelectorAll) return null;
    const selector = [
        'input:not([type="hidden"]):not([disabled]):not([readonly])',
        'select:not([disabled])',
        'textarea:not([disabled]):not([readonly])',
    ].join(',');

    return Array.from(scope.querySelectorAll(selector)).find((el) => isVisible(el)) || null;
}

function firstInvalidField(form) {
    if (!form || !form.querySelectorAll) return null;
    const invalid = Array.from(form.querySelectorAll(':invalid'));
    return invalid.find((el) => isVisible(el)) || null;
}

function labelForField(field) {
    if (!field) return null;
    const id = field.getAttribute('id');
    if (id) {
        const linked = document.querySelector(`label[for="${id}"]`);
        if (linked) return linked.textContent?.trim() || null;
    }
    const group = field.closest('.mb-3, .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12');
    const localLabel = group ? group.querySelector('.form-label') : null;
    return localLabel ? (localLabel.textContent || '').trim() : null;
}

function isConfigForm(form) {
    return !!form;
}

function getMethod(form) {
    const spoofed = form.querySelector('input[name="_method"]');
    const method = (spoofed?.value || form.getAttribute('method') || 'GET').toUpperCase();
    return method;
}

function isCrudActionForm(form, submitter) {
    if (!form || form.dataset.confirm === 'off' || form.hasAttribute('data-confirm-ignore')) return false;
    const action = (form.getAttribute('action') || '').toLowerCase();
    if (action.includes('/logout')) return false;

    const method = getMethod(form);
    if (method === 'GET') return false;

    // Exclude explicit auth forms
    const authScope = form.closest('.auth-page-wrapper, .signin-main, .login-content');
    if (authScope) return false;

    // If no submitter, still apply for non-GET forms.
    if (!submitter) return true;

    // Optional opt-out per button
    if (submitter.hasAttribute('data-confirm-ignore')) return false;
    return true;
}

function skipTwoStageConfirm(form, submitter) {
    if (!form) return false;
    const action = (form.getAttribute('action') || '').toLowerCase();

    // Nella schedina il feedback post-submit e' sufficiente:
    // evitare doppia conferma per salva componente / salva schedina.
    if (action.includes('/schedine') && getMethod(form) !== 'DELETE') {
        return true;
    }

    return false;
}

function inferActionType(form, submitter) {
    const method = getMethod(form);
    const buttonText = ((submitter?.innerText || submitter?.value || '') + '').toLowerCase().trim();
    const buttonClass = (submitter?.className || '').toLowerCase();
    const action = (form.getAttribute('action') || '').toLowerCase();
    const submitterName = (submitter?.getAttribute('name') || '').toLowerCase();
    const submitterValue = (submitter?.getAttribute('value') || '').toLowerCase();

    if (submitterName === 'save_mode' && submitterValue === 'draft') {
        return 'draft';
    }

    if (submitterName === 'save_mode' && submitterValue === 'to_schedina') {
        return 'to_schedina';
    }

    if (submitterName === 'save_mode' && submitterValue === 'to_arrivi') {
        return 'to_arrivi';
    }

    if (
        method === 'DELETE' ||
        buttonText.includes('elimina') ||
        buttonText.includes('cancella') ||
        buttonClass.includes('danger') ||
        action.includes('destroy') ||
        action.includes('delete')
    ) {
        return 'delete';
    }

    if (
        method === 'PUT' ||
        method === 'PATCH' ||
        buttonText.includes('modifica') ||
        buttonText.includes('aggiorna') ||
        action.includes('update')
    ) {
        return 'update';
    }

    return 'save';
}

function readOverride(form = null, submitter = null, key) {
    return submitter?.dataset?.[key] || form?.dataset?.[key] || '';
}

function prettifyResource(resource) {
    return (resource || '')
        .replace(/^js\s+/i, '')
        .replace(/[_-]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function inferSubjectFromAction(action = '') {
    const path = (action || '').toLowerCase();

    if (path.includes('/cestino/')) return 'questo elemento del cestino';
    if (path.includes('/web_checkin') || path.includes('/web-checkin')) return 'questa richiesta Web Check-in';
    if (path.includes('/arrivals')) return 'questo arrivo';
    if (path.includes('/customer') || path.includes('/customers')) return 'questo cliente';
    if (path.includes('/schedina') || path.includes('/schedine')) return 'questa schedina';
    if (path.includes('/struttura')) return 'questa struttura';
    if (path.includes('/gruppi')) return 'questo gruppo';
    if (path.includes('/tipo_cliente')) return 'questo tipo cliente';
    if (path.includes('/tipo_documento')) return 'questo tipo documento';
    if (path.includes('/tipovia') || path.includes('/tipo_via')) return 'questo tipo via';
    if (path.includes('/titolo') || path.includes('/title')) return 'questo titolo';
    if (path.includes('/rilasciato')) return 'questo rilascio documento';
    if (path.includes('/questura')) return 'questa operazione Questura';

    const segments = path
        .split('?')[0]
        .split('/')
        .filter(Boolean)
        .filter((segment) => !/^\d+$/.test(segment) && !['store', 'update', 'destroy', 'delete'].includes(segment));

    const resource = prettifyResource(segments[segments.length - 1] || '');
    return resource ? `questo elemento di ${resource}` : 'questo elemento';
}

function applyOverrides(copy, form = null, submitter = null) {
    const overridden = { ...copy };
    const fields = [
        ['confirmTitle', 'confirmTitle'],
        ['confirmText', 'confirmText'],
        ['confirmButton', 'confirmButton'],
        ['doneTitle', 'doneTitle'],
        ['doneText', 'doneText'],
        ['successTitle', 'successTitle'],
        ['successText', 'successText'],
    ];

    fields.forEach(([target, key]) => {
        const value = readOverride(form, submitter, key);
        if (value) {
            overridden[target] = value;
        }
    });

    return overridden;
}

function contextualCopy(type, form = null, submitter = null) {
    const subject = readOverride(form, submitter, 'confirmLabel') || inferSubjectFromAction(form?.getAttribute('action') || '');

    if (type === 'delete') {
        return {
            confirmTitle: 'Conferma eliminazione',
            confirmText: `Stai per eliminare ${subject}. Vuoi continuare?`,
            confirmButton: 'Sì, elimina',
            doneTitle: 'Eliminazione confermata',
            doneText: `Premi OK per procedere con l'eliminazione di ${subject}.`,
            successTitle: 'Eliminato',
            successText: 'Operazione completata correttamente.',
        };
    }

    if (type === 'update') {
        return {
            confirmTitle: 'Conferma modifica',
            confirmText: `Stai per modificare ${subject}. Vuoi continuare?`,
            confirmButton: 'Sì, modifica',
            doneTitle: 'Modifica confermata',
            doneText: `Premi OK per procedere con la modifica di ${subject}.`,
            successTitle: 'Modificato',
            successText: 'Operazione completata correttamente.',
        };
    }

    return null;
}

function buildAlertCopy(type, form = null, submitter = null) {
    const confirmKind = form?.dataset?.confirmKind || '';

    if (confirmKind === 'import-schedina') {
        return {
            confirmTitle: 'Importa in schedina',
            confirmText: 'La richiesta Web Check-in verrà convertita in schedina ufficiale. Vuoi procedere?',
            confirmButton: 'Sì, importa',
            doneTitle: 'Importazione confermata',
            doneText: 'Premi OK per completare l\'importazione nella schedina ufficiale.',
            successTitle: 'Importata',
            successText: 'La richiesta è stata importata correttamente.',
        };
    }

    if (confirmKind === 'questura-verify') {
        return {
            confirmTitle: 'Verifica Questura WS',
            confirmText: 'Il sistema controllerà il file del periodo tramite Web Service ufficiale. Vuoi procedere?',
            confirmButton: 'Sì, verifica',
            doneTitle: 'Verifica confermata',
            doneText: 'Premi OK per avviare la verifica automatica.',
            successTitle: 'Verifica completata',
            successText: 'Controlla l\'esito nel pannello Questura.',
        };
    }

    if (confirmKind === 'questura-send') {
        return {
            confirmTitle: 'Invia a Questura',
            confirmText: 'Il sistema trasmetterà il file del periodo al Web Service ufficiale. Vuoi procedere?',
            confirmButton: 'Sì, invia',
            doneTitle: 'Invio confermato',
            doneText: 'Premi OK per avviare la trasmissione automatica.',
            successTitle: 'Invio avviato',
            successText: 'Controlla esito e ricevuta nel pannello Questura.',
        };
    }

    const contextual = contextualCopy(type, form, submitter);
    if (contextual) {
        return applyOverrides(contextual, form, submitter);
    }

    if (type === 'draft') {
        if ((form?.getAttribute('action') || '').toLowerCase().includes('/schedine')) {
            return applyOverrides({
                confirmTitle: 'Conferma salvataggio bozza',
                confirmText: 'La schedina verrà salvata come bozza provvisoria. Vuoi continuare?',
                confirmButton: 'Sì, salva bozza',
                doneTitle: 'Bozza confermata',
                doneText: 'Premi OK per procedere con il salvataggio provvisorio.',
                successTitle: 'Bozza schedina salvata',
                successText: 'La schedina è stata salvata come bozza.',
            }, form, submitter);
        }

        return applyOverrides({
            confirmTitle: 'Conferma salvataggio bozza',
            confirmText: 'Il cliente verrà salvato come bozza provvisoria. Vuoi continuare?',
            confirmButton: 'Sì, salva bozza',
            doneTitle: 'Bozza confermata',
            doneText: 'Premi OK per procedere con il salvataggio provvisorio.',
            successTitle: 'Bozza salvata',
            successText: 'La bozza è stata salvata correttamente.',
        }, form, submitter);
    }

    if (type === 'to_schedina') {
        return applyOverrides({
            confirmTitle: 'Salva e apri schedina',
            confirmText: 'Il cliente verrà salvato e subito aperto in una nuova schedina precompilata. Vuoi continuare?',
            confirmButton: 'Sì, salva in schedina',
            doneTitle: 'Passaggio a schedina confermato',
            doneText: 'Premi OK per salvare il cliente e aprire la schedina nuova.',
            successTitle: 'Cliente salvato in schedina',
            successText: 'La schedina nuova è pronta con i dati del cliente.',
        }, form, submitter);
    }

    if (type === 'to_arrivi') {
        return applyOverrides({
            confirmTitle: 'Salva in arrivi',
            confirmText: 'La schedina verrà salvata nel circuito Arrivi. Vuoi continuare?',
            confirmButton: 'Sì, salva in arrivi',
            doneTitle: 'Passaggio ad arrivi confermato',
            doneText: 'Premi OK per salvare la schedina nel circuito Arrivi.',
            successTitle: 'Schedina salvata in arrivi',
            successText: 'La registrazione è stata spostata nel circuito Arrivi.',
        }, form, submitter);
    }

    return applyOverrides({
        confirmTitle: 'Conferma salvataggio',
        confirmText: 'Stai per salvare i dati. Vuoi continuare?',
        confirmButton: 'Sì, salva',
        doneTitle: 'Salvataggio confermato',
        doneText: 'Premi OK per procedere con il salvataggio.',
        successTitle: 'Salvato',
        successText: 'Operazione completata correttamente.',
    }, form, submitter);
}

async function confirmTwoStage(type, form = null, submitter = null) {
    const copy = buildAlertCopy(type, form, submitter);

    const first = await Swal.fire({
        title: copy.confirmTitle,
        text: copy.confirmText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: copy.confirmButton,
        cancelButtonText: 'Annulla',
        reverseButtons: true,
        focusCancel: true,
    });

    if (!first.isConfirmed) return false;

    const second = await Swal.fire({
        title: copy.doneTitle,
        text: copy.doneText,
        icon: 'success',
        confirmButtonText: 'OK',
    });

    return second.isConfirmed;
}

function stripLegacyInlineConfirm(scope) {
    if (!scope || !scope.querySelectorAll) return;

    scope.querySelectorAll('form[onsubmit*="confirm("]').forEach((form) => {
        form.removeAttribute('onsubmit');
    });

    scope.querySelectorAll('button[onclick*="confirm("], input[type="submit"][onclick*="confirm("]').forEach((el) => {
        el.removeAttribute('onclick');
    });
}

function bindCrudLinks(scope) {
    if (!scope || !scope.querySelectorAll) return;

    const links = scope.querySelectorAll('a[href]:not([data-confirm-ignore])');
    links.forEach((link) => {
        if (!initOnce(link, 'crud-link-ux')) return;

        const href = (link.getAttribute('href') || '').toLowerCase();
        const klass = (link.className || '').toLowerCase();
        const isDanger = klass.includes('btn-danger') || klass.includes('btn-soft-danger');
        const looksDelete = /destroy|delete|elimina|remove/.test(href);

        if (!isDanger && !looksDelete) return;
        if (href.includes('/logout')) return;

        link.addEventListener('click', async (event) => {
            event.preventDefault();
            const ok = await confirmTwoStage('delete', null, link);
            if (ok) window.location.assign(link.href);
        });
    });
}

function showServerAlerts(scope) {
    if (scope !== document) return;
    if (document.body.dataset.serverAlertShown === '1') return;
    document.body.dataset.serverAlertShown = '1';

    const success = document.querySelector('[data-server-alert="success"]');
    const warning = document.querySelector('[data-server-alert="warning"]');
    const danger = document.querySelector('[data-server-alert="error"]');

    if (success) {
        const text = (success.textContent || '').trim();
        if (text) {
            Swal.fire({
                title: 'Operazione completata',
                text,
                icon: 'success',
                confirmButtonText: 'OK',
            });
            success.style.display = 'none';
        }
        return;
    }

    if (warning) {
        const text = (warning.textContent || '').replace(/\s+/g, ' ').trim();
        if (text) {
            Swal.fire({
                title: 'Bozza salvata',
                text,
                icon: 'info',
                confirmButtonText: 'OK',
            });
            warning.style.display = 'none';
        }
        return;
    }

    if (danger) {
        const text = (danger.textContent || '').replace(/\s+/g, ' ').trim();
        if (text) {
            Swal.fire({
                title: 'Attenzione',
                text,
                icon: 'error',
                confirmButtonText: 'OK',
            });
            danger.style.display = 'none';
        }
    }
}

function bindFormBehavior(form) {
    if (!initOnce(form, 'config-ux')) return;
    form.setAttribute('novalidate', 'novalidate');
    const skipClientValidation = form.dataset.clientValidate === 'off';

    form.addEventListener('submit', async (event) => {
        if (!isConfigForm(form)) return;
        const submitter = event.submitter || null;
        const isDraftAction = !!(
            submitter &&
            (
                (submitter.getAttribute('name') === 'save_mode' && (submitter.getAttribute('value') || '').toLowerCase() === 'draft') ||
                submitter.hasAttribute('formnovalidate')
            )
        );

        if (!skipClientValidation && !isDraftAction && !form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            const invalidField = firstInvalidField(form);
            if (invalidField) invalidField.focus();
            const fieldLabel = labelForField(invalidField);
            Swal.fire({
                title: 'Campi obbligatori mancanti',
                text: fieldLabel
                    ? `Completa il campo richiesto: ${fieldLabel}`
                    : 'Completa i campi obbligatori evidenziati prima di salvare.',
                icon: 'warning',
                confirmButtonText: 'OK',
            });
            return;
        }

        if (
            !skipTwoStageConfirm(form, submitter) &&
            form.dataset.confirmedAction !== '1' &&
            isCrudActionForm(form, event.submitter)
        ) {
            event.preventDefault();
            event.stopPropagation();

            const type = inferActionType(form, event.submitter);
            const confirmed = await confirmTwoStage(type, form, event.submitter);
            if (!confirmed) return;

            form.dataset.confirmedAction = '1';
            if (typeof form.requestSubmit === 'function') {
                if (event.submitter) {
                    form.requestSubmit(event.submitter);
                } else {
                    form.requestSubmit();
                }
            } else {
                form.submit();
            }
            return;
        }

        if (form.dataset.confirmedAction === '1') {
            delete form.dataset.confirmedAction;
        }

        if (form.dataset.submitting === '1') {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = '1';
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach((btn) => {
            btn.setAttribute('disabled', 'disabled');
            btn.setAttribute('aria-disabled', 'true');
        });
    });
}

function bindModalBehavior(modal) {
    if (!initOnce(modal, 'config-modal-ux')) return;

    modal.addEventListener('shown.bs.modal', () => {
        const focusTarget = firstFocusableField(modal);
        if (focusTarget) {
            focusTarget.focus();
            if (focusTarget.select && focusTarget.tagName === 'INPUT') {
                focusTarget.select();
            }
        }
    });

    modal.addEventListener('hidden.bs.modal', () => {
        modal.querySelectorAll('form').forEach((form) => {
            form.classList.remove('was-validated');
            delete form.dataset.submitting;
            delete form.dataset.confirmedAction;
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((btn) => {
                btn.removeAttribute('disabled');
                btn.removeAttribute('aria-disabled');
            });
        });
    });
}

export function initConfigUx(root = document) {
    const scope = root || document;
    if (!scope.querySelectorAll) return;

    stripLegacyInlineConfirm(scope);
    scope.querySelectorAll('form').forEach(bindFormBehavior);
    scope.querySelectorAll('.modal').forEach(bindModalBehavior);
    bindCrudLinks(scope);
    showServerAlerts(scope);
}
