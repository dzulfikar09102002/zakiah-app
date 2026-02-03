"use client"

import { ChevronRight } from "lucide-react"
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

export default function NavItemNode({ item }: { item: NavItem }) {
    const hasChildren = item.items && item.items.length > 0

    if (!hasChildren) {
        return (
            <SidebarMenuItem>
                <SidebarMenuButton asChild className="w-full justify-start">
                    <a href={item.href} className="flex items-center gap-2 w-full">
                        {item.icon && <item.icon className="size-4" />}
                        <span>{item.title}</span>
                    </a>
                </SidebarMenuButton>
            </SidebarMenuItem>
        )
    }

    return (
        <Collapsible
            asChild
            defaultOpen={item.isActive}
            className="group/collapsible"
        >
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton tooltip={item.title}>
                        {item.icon && <item.icon className="mr-2 size-4" />}
                        <span>{item.title}</span>
                        <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub className="pl-4">
                        {item.items?.map((child) => (
                            <SidebarMenuSubItem key={child.title}>
                                {child.items && child.items.length > 0 ? (
                                    <NavItemNode item={child} />
                                ) : (
                                    <SidebarMenuSubButton asChild>
                                        <a href={child.href}>
                                            <span>{child.title}</span>
                                        </a>
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