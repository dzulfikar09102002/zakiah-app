import { motion } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Mail } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';

const title = 'Segera Hadir';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title,
        href: dashboard().url,
    },
];

export default () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={title} />
            {/* Menggunakan h-screen dan overflow-hidden untuk mencegah scroll */}
            <div className="fixed inset-0 flex items-center justify-center bg-slate-50 px-6 dark:bg-slate-950">
                {/* Animated Gradient Background */}
                <motion.div
                    className="absolute inset-0 -z-10 bg-gradient-to-br from-indigo-500/30 via-purple-500/30 to-pink-500/30 blur-[100px]"
                    animate={{
                        scale: [1, 1.2, 1],
                        rotate: [0, 45, 0],
                    }}
                    transition={{
                        duration: 15,
                        repeat: Infinity,
                        ease: 'linear',
                    }}
                />

                {/* Card dengan tinggi yang pas agar tidak memicu scroll */}
                <Card className="relative w-full max-w-2xl border border-white/40 bg-white/60 shadow-2xl backdrop-blur-2xl md:ml-[calc(16rem/2)] lg:ml-[calc(33rem/2)] dark:border-white/10 dark:bg-black/40">
                    <CardContent className="flex flex-col items-center p-8 text-center md:p-12">
                        {/* Floating Emoji */}
                        <motion.div
                            animate={{ y: [0, -12, 0] }}
                            transition={{
                                duration: 3,
                                repeat: Infinity,
                                ease: 'easeInOut',
                            }}
                            className="mb-6 text-5xl md:text-6xl"
                        >
                            🚀
                        </motion.div>

                        {/* Animated Title */}
                        <motion.h1
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.5 }}
                            className="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text pb-1 text-4xl font-extrabold tracking-tight text-transparent md:text-6xl"
                        >
                            Coming Soon
                        </motion.h1>

                        {/* Subtitle */}
                        <motion.p
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                            className="mx-auto mt-4 max-w-md text-base leading-relaxed text-slate-600 md:text-lg dark:text-slate-300"
                        >
                            Kami sedang menyiapkan sesuatu yang luar biasa untuk
                            Anda.
                        </motion.p>

                        {/* Animated Divider */}
                        <motion.div
                            initial={{ width: 0 }}
                            animate={{ width: '4rem' }}
                            transition={{ duration: 0.8, delay: 0.4 }}
                            className="mt-6 h-1.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500"
                        />

                        {/* Floating Badge */}
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ delay: 0.6 }}
                            className="mt-8"
                        >
                            <span className="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50/50 px-5 py-2 text-sm font-semibold text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <span className="relative flex h-2 w-2">
                                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                                    <span className="relative inline-flex h-2 w-2 rounded-full bg-indigo-500"></span>
                                </span>
                                Stay Tuned ✨
                            </span>
                        </motion.div>

                        {/* CTA Button */}
                        <motion.div
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.8 }}
                            className="mt-8"
                        ></motion.div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
};
