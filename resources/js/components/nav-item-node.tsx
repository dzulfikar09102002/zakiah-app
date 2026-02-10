import { Link, usePage } from "@inertiajs/react"
import { ChevronRight } from "lucide-react"
import { useState, useMemo } from "react"
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/ui/collapsible"
import {
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from "@/components/ui/sidebar"
import type { NavItem } from "@/types"

// helper: cek aktif sampai ke anak-anak
function isItemActive(item: NavItem, currentPath: string): boolean {
    if (item.href && item.href !== "#" && currentPath.startsWith(item.href)) {
        return true
    }
    if (item.items?.length) {
        return item.items.some((child) => isItemActive(child, currentPath))
    }
    return false
}

export default function NavItemNode({ item }: { item: NavItem }) {
    const { url } = usePage()
    const hasChildren = !!item.items?.length

    const shouldBeOpen = useMemo(
        () => hasChildren && isItemActive(item, url),
        [hasChildren, item, url]
    )

    const [open, setOpen] = useState(false)

    const isOpen = useMemo(() => {
        return open || (hasChildren && isItemActive(item, url))
    }, [open, hasChildren, item, url])

    if (!hasChildren) {
        return (
            <SidebarMenuItem>
                <SidebarMenuButton
                    asChild
                    isActive={isItemActive(item, url)}
                    className="w-full justify-start"
                >
                    <Link href={item.href} className="flex items-center gap-2 w-full">
                        {item.icon && <item.icon className="size-4" />}
                        <span>{item.title}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        )
    }

    return (
        <Collapsible
            asChild
            open={isOpen}
            onOpenChange={setOpen}
            className="group/collapsible"
        >
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton
                        tooltip={item.title}
                        className="relative pr-10"
                    >
                        {item.icon && <item.icon className="mr-2 size-4" />}
                        <span>{item.title}</span>
                        <ChevronRight className="absolute right-3 top-1/2 -translate-y-1/2 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>

                <CollapsibleContent>
                    <SidebarMenuSub className="pl-4">
                        {item.items?.map((child) => (
                            <SidebarMenuSubItem key={child.title}>
                                {child.items?.length ? (
                                    <NavItemNode item={child} />
                                ) : (
                                    <SidebarMenuSubButton
                                        asChild
                                        isActive={isItemActive(child, url)}
                                    >
                                        <Link href={child.href} className="flex items-center gap-2">
                                            {child.icon && <child.icon className="size-4" />}
                                            <span>{child.title}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                )}
                            </SidebarMenuSubItem>
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    )
}