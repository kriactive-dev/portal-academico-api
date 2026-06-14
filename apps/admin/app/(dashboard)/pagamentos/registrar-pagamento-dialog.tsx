"use client"

import { useState } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { toast } from "sonner"
import { z } from "zod"

import { usePagamentosStore } from "@workspace/mock-data/stores"
import type { Pagamento } from "@workspace/mock-data/types"
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
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@workspace/ui/components/form"
import { Input } from "@workspace/ui/components/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@workspace/ui/components/select"
import { formatarMoeda } from "@workspace/ui/lib/utils"

export const METODO_LABELS = {
  transferencia: "Transferência bancária",
  numerario: "Numerário",
  mpesa: "M-Pesa",
  emola: "e-Mola",
  deposito: "Depósito",
} as const

const registarPagamentoSchema = z.object({
  metodo: z.enum(["transferencia", "numerario", "mpesa", "emola", "deposito"]),
  dataPagamento: z.string().min(1, "Indique a data do pagamento."),
})

type RegistarPagamentoValues = z.infer<typeof registarPagamentoSchema>

interface RegistrarPagamentoDialogProps {
  pagamento: Pagamento
}

export function RegistrarPagamentoDialog({
  pagamento,
}: RegistrarPagamentoDialogProps) {
  const [open, setOpen] = useState(false)
  const registarPagamento = usePagamentosStore(
    (state) => state.registarPagamento
  )

  const form = useForm<RegistarPagamentoValues>({
    resolver: zodResolver(registarPagamentoSchema),
    defaultValues: {
      metodo: "numerario",
      dataPagamento: new Date().toISOString().slice(0, 10),
    },
  })

  function onSubmit(values: RegistarPagamentoValues) {
    registarPagamento(pagamento.id, values)
    toast.success("Pagamento registado com sucesso.")
    setOpen(false)
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button size="sm">Registar Pagamento</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Registar Pagamento</DialogTitle>
          <DialogDescription>
            Confirme o método e a data do pagamento de{" "}
            {formatarMoeda(pagamento.valor)} referente a{" "}
            {pagamento.mesReferencia}.
          </DialogDescription>
        </DialogHeader>
        <Form {...form}>
          <form
            onSubmit={form.handleSubmit(onSubmit)}
            className="flex flex-col gap-4"
          >
            <FormField
              control={form.control}
              name="metodo"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Método de pagamento</FormLabel>
                  <Select onValueChange={field.onChange} value={field.value}>
                    <FormControl>
                      <SelectTrigger className="w-full">
                        <SelectValue />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      {Object.entries(METODO_LABELS).map(([value, label]) => (
                        <SelectItem key={value} value={value}>
                          {label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="dataPagamento"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Data do pagamento</FormLabel>
                  <FormControl>
                    <Input type="date" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <DialogFooter>
              <Button
                type="button"
                variant="outline"
                onClick={() => setOpen(false)}
              >
                Cancelar
              </Button>
              <Button type="submit">Confirmar</Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  )
}
