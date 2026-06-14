"use client"

import { useState } from "react"
import { XIcon } from "lucide-react"
import { toast } from "sonner"

import { usePedidosStore } from "@workspace/mock-data/stores"
import { Button } from "@workspace/ui/components/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@workspace/ui/components/dialog"
import { Label } from "@workspace/ui/components/label"
import { Textarea } from "@workspace/ui/components/textarea"

interface NegarPedidoDialogProps {
  pedidoId: string
}

export function NegarPedidoDialog({ pedidoId }: NegarPedidoDialogProps) {
  const [open, setOpen] = useState(false)
  const [motivo, setMotivo] = useState("")
  const negarPedido = usePedidosStore((state) => state.negarPedido)

  function onConfirm() {
    negarPedido(pedidoId, motivo, new Date().toISOString().slice(0, 10))
    toast.success("Pedido negado.")
    setOpen(false)
    setMotivo("")
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="destructive">
          <XIcon />
          Negar
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Negar pedido</DialogTitle>
          <DialogDescription>
            Indique o motivo da negação. O estudante poderá ver esta
            informação.
          </DialogDescription>
        </DialogHeader>
        <div className="flex flex-col gap-2">
          <Label htmlFor="motivo">Motivo</Label>
          <Textarea
            id="motivo"
            value={motivo}
            onChange={(event) => setMotivo(event.target.value)}
            placeholder="Descreva o motivo da negação..."
          />
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => setOpen(false)}>
            Cancelar
          </Button>
          <Button
            variant="destructive"
            onClick={onConfirm}
            disabled={!motivo.trim()}
          >
            Confirmar negação
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
