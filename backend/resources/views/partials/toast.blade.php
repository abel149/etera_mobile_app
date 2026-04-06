{{-- etera Toast Notification System --}}
{{-- Reads Laravel flash session messages and shows React-based toasts --}}

<div id="etera-toast-root"></div>

<script>
    // Pass Laravel flash messages to JS
    window.__ETERA_TOASTS__ = [];
    @if(session('success'))
        window.__ETERA_TOASTS__.push({ type: 'success', message: @json(session('success')) });
    @endif
    @if(session('error'))
        window.__ETERA_TOASTS__.push({ type: 'error', message: @json(session('error')) });
    @endif
    @if(session('warning'))
        window.__ETERA_TOASTS__.push({ type: 'warning', message: @json(session('warning')) });
    @endif
    @if(session('info'))
        window.__ETERA_TOASTS__.push({ type: 'info', message: @json(session('info')) });
    @endif
    @if($errors->any())
        @foreach($errors->all() as $error)
            window.__ETERA_TOASTS__.push({ type: 'error', message: @json($error) });
        @endforeach
    @endif
</script>

@verbatim
<script type="text/babel">
    const { useState, useEffect, useCallback } = React;

    const TOAST_ICONS = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ',
    };

    function Toast({ toast, onDismiss }) {
        const [isDismissing, setIsDismissing] = useState(false);

        const dismiss = useCallback(() => {
            setIsDismissing(true);
            setTimeout(() => onDismiss(toast.id), 300);
        }, [toast.id, onDismiss]);

        useEffect(() => {
            const timer = setTimeout(dismiss, 4000);
            return () => clearTimeout(timer);
        }, [dismiss]);

        return (
            <div className={`etera-toast etera-toast-${toast.type} ${isDismissing ? 'dismissing' : ''}`} style={{ position: 'relative' }}>
                <span className="etera-toast-icon">{TOAST_ICONS[toast.type]}</span>
                <span className="etera-toast-message">{toast.message}</span>
                <button className="etera-toast-close" onClick={dismiss}>×</button>
                <div className="etera-toast-progress" style={{ animationDuration: '4s' }}></div>
            </div>
        );
    }

    function ToastContainer() {
        const [toasts, setToasts] = useState([]);

        useEffect(() => {
            if (window.__ETERA_TOASTS__ && window.__ETERA_TOASTS__.length > 0) {
                const initialToasts = window.__ETERA_TOASTS__.map((t, i) => ({
                    ...t,
                    id: Date.now() + i,
                }));
                setToasts(initialToasts);
                window.__ETERA_TOASTS__ = [];
            }
        }, []);

        // Expose global method to trigger toasts from anywhere
        useEffect(() => {
            window.eteraToast = (type, message) => {
                setToasts(prev => [...prev, { id: Date.now(), type, message }]);
            };
        }, []);

        const dismiss = useCallback((id) => {
            setToasts(prev => prev.filter(t => t.id !== id));
        }, []);

        return (
            <div className="etera-toast-container">
                {toasts.map(t => (
                    <Toast key={t.id} toast={t} onDismiss={dismiss} />
                ))}
            </div>
        );
    }

    // Mount toast system
    const toastRoot = document.getElementById('etera-toast-root');
    if (toastRoot) {
        ReactDOM.createRoot(toastRoot).render(<ToastContainer />);
    }
</script>
@endverbatim
