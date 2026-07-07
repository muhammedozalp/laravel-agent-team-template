import { Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { logout } from '@/routes';

export default function ApprovalPending() {
    return (
        <>
            <Head title="Waiting for approval" />

            <div className="space-y-6 text-center">
                <TextLink href={logout()} className="mx-auto block text-sm">
                    Log out
                </TextLink>
            </div>
        </>
    );
}

ApprovalPending.layout = {
    title: 'Waiting for approval',
    description:
        'Thanks for signing up! An administrator needs to approve your account before you can continue. We will email you once it is ready.',
};
