import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import AuthSimpleLayout from '@/layouts/auth/auth-simple-layout';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    const [showPassword, setShowPassword] = useState(false);

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
                                    className="text-[12px] font-bold tracking-wide uppercase"
                                    style={{ color: '#9e5f48' }}
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
                                    placeholder="email@example.com"
                                    className="h-11 rounded-xl text-[13px] transition-all duration-200"
                                    style={{
                                        border: '1.5px solid rgba(196,134,106,0.3)',
                                        background: 'rgba(253,244,240,0.6)',
                                    }}
                                />
                                <InputError message={errors.email} />
                            </div>

                            {/* Password */}
                            <div className="grid gap-1.5">
                                <div className="flex items-center justify-between">
                                    <Label
                                        htmlFor="password"
                                        className="text-[12px] font-bold tracking-wide uppercase"
                                        style={{ color: '#9e5f48' }}
                                    >
                                        Password
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="text-[12px] font-medium transition-colors"
                                            style={{ color: '#c4866a' }}
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
                                        placeholder="Masukkan password"
                                        className="h-11 rounded-xl pr-11 text-[13px] transition-all duration-200"
                                        style={{
                                            border: '1.5px solid rgba(196,134,106,0.3)',
                                            background: 'rgba(253,244,240,0.6)',
                                        }}
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
                                    className="relative h-11 w-full overflow-hidden rounded-xl border-none text-[13px] font-bold tracking-widest text-white uppercase transition-all duration-200 active:scale-[0.98]"
                                    style={{
                                        background: processing
                                            ? '#d9a48a'
                                            : 'linear-gradient(135deg, #d4956e 0%, #c4866a 40%, #b87058 70%, #c4956a 100%)',
                                        boxShadow: processing
                                            ? 'none'
                                            : '0 4px 20px rgba(196,134,106,0.45), 0 1px 4px rgba(120,60,30,0.2)',
                                    }}
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
                <div
                    className="mt-4 rounded-xl px-4 py-3 text-center text-[13px] font-medium"
                    style={{
                        background: 'rgba(196,134,106,0.08)',
                        border: '1px solid rgba(196,134,106,0.25)',
                        color: '#9e5f48',
                    }}
                >
                    {status}
                </div>
            )}
        </AuthSimpleLayout>
    );
}
