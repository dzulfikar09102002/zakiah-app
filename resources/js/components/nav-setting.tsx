"use client"

import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
} from "@/components/ui/sidebar"
import type { NavItem } from "@/types"
import NavItemSetting from "./nav-item-setting"

export function NavSetting({ items }: { items: NavItem[] }) {
    return (
        <SidebarGroup>
            <SidebarGroupLabel>Pengaturan</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <NavItemSetting key={item.title} item={item} />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    )
}