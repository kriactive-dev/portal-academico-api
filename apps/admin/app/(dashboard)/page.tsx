import {
  Card,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"

export default function DashboardPage() {
  return (
    <div className="flex flex-1 flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Bem-vindo ao Ya Académico</CardTitle>
          <CardDescription>
            Painel de gestão académica — em construção.
          </CardDescription>
        </CardHeader>
      </Card>
    </div>
  )
}
