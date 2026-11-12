import { Form, Head } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useEffect, useState } from 'react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSimpleLayout from '@/layouts/auth/auth-simple-layout';
import { resetOpenSections } from '@/lib/sidebar-sections';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    const [showPassword, setShowPassword] = useState(false);

    // Setelah login, semua menu sidebar mulai dalam keadaan tertutup.
    useEffect(() => resetOpenSections(), []);

    return (
        <AuthSimpleLayout
            title="Selamat Datang"
            description="Masukkan email dan password untuk masuk ke akun Anda"
        >
            <Head title="Log in" />

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-5"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-4">
                            {/* Email */}
                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor="email"
                                    className="text-[12px] font-semibold tracking-wide text-foreground"
                                >
                                    Alamat Email
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="Masukkan email"
                                    className="h-11 rounded-xl border-input bg-muted/40 text-[13px] shadow-none transition-all duration-200 focus-visible:bg-card"
                                />
                                <InputError message={errors.email} />
                            </div>

                            {/* Password */}
                            <div className="grid gap-1.5">
                                <div className="flex items-center justify-between">
                                    <Label
                                        htmlFor="password"
                                        className="text-[12px] font-semibold tracking-wide text-foreground"
                                    >
                                        Password
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="text-[12px] font-medium text-primary no-underline transition-colors hover:underline"
                                            tabIndex={5}
                                        >
                                            Lupa password?
                                        </TextLink>
                                    )}
                                </div>
                                <div className="relative">
                                    <Input
                                        id="password"
                                        type={
                                            showPassword ? 'text' : 'password'
                                        }
                                        name="password"
                                        required
                                        tabIndex={2}
                                        autoComplete="current-password"
                                        placeholder="********"
                                        className="h-11 rounded-xl border-input bg-muted/40 pr-11 text-[13px] shadow-none transition-all duration-200 focus-visible:bg-card"
                                    />
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setShowPassword(!showPassword)
                                        }
                                        tabIndex={-1}
                                        className="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        {showPassword ? (
                                            <EyeOff size={15} />
                                        ) : (
                                            <Eye size={15} />
                                        )}
                                    </button>
                                </div>
                                <InputError message={errors.password} />
                            </div>

                            {/* Submit button — with shine effect */}
                            <div className="relative mt-1 overflow-hidden rounded-xl">
                                <Button
                                    type="submit"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="login-button"
                                    className="relative h-11 w-full overflow-hidden rounded-xl border-none bg-gradient-to-r from-primary to-[#1f7f8c] text-[13px] font-semibold tracking-widest text-primary-foreground shadow-lg shadow-primary/30 transition-all duration-200 hover:shadow-xl hover:shadow-primary/35 active:scale-[0.98] disabled:shadow-none dark:to-primary"
                                >
                                    {/* Shine sweep overlay */}
                                    {!processing && (
                                        <span
                                            className="pointer-events-none absolute top-0 bottom-0 w-16 -skew-x-12 opacity-25"
                                            style={{
                                                background:
                                                    'linear-gradient(90deg, transparent, white, transparent)',
                                                animation:
                                                    'auth-shine 3s ease-in-out 1s infinite',
                                            }}
                                        />
                                    )}
                                    <span className="flex items-center justify-center gap-2">
                                        {processing && (
                                            <Spinner className="size-4" />
                                        )}
                                        {processing ? 'Login...' : 'Masuk'}
                                    </span>
                                </Button>
                            </div>
                        </div>
                    </>
                )}
            </Form>

            {status && (
                <div className="mt-4 rounded-xl border border-success/25 bg-success/10 px-4 py-3 text-center text-[13px] font-medium text-success">
                    {status}
                </div>
            )}
        </AuthSimpleLayout>
    );
}
