"use client"

import {
    SidebarGroup,
    SidebarMenu,
} from "@/components/ui/sidebar"
import type { NavItem } from "@/types"
import NavItemNode from "./nav-item-node"

export function NavMain({ items }: { items: NavItem[] }) {
    return (
        <SidebarGroup>
            <SidebarMenu className="gap-2">
                {items.map((item) => (
                    <NavItemNode key={item.title} item={item} />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    )
}