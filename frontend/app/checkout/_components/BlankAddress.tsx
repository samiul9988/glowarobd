import BodyText from '@/components/BodyText'
import { DialogTrigger } from '@/components/ui/dialog'
import { Plus } from 'lucide-react'
import Image from 'next/image'
import React from 'react'

export default function BlankAddress({addresses, handleAddNewAddress} : {addresses: any, handleAddNewAddress: () => void}) {
  return (
    <div>
        <div className="bg-white border-site-gray-100 flex min-h-[200px] flex-col items-center justify-center gap-1 rounded-md border-1 py-4">
            <Image
                src="/images/blank-location.gif"
                width={88}
                height={88}
                alt="address not found"
                className="mb-3"
            />
            <h3
            className="text-lg md:text-[23px] text-site-gray-700 font-bold"
            >
            No Address Added Yet
            </h3>
            <BodyText
            className="text-site-gray-400 text-sm font-normal"
            variant="one"
            >
            Add an address to get your order delivered.
            </BodyText>
            {addresses?.length === 0 &&
            <DialogTrigger asChild>

                <button
                    type="button"
                    onClick={handleAddNewAddress}
                    className="font-medium  flex cursor-pointer items-center gap-1.5  rounded-[10px] px-2 py-1.5 text-base text-site-primary-600 hover:text-site-primary-500 transition duration-300 lg:gap-2 md:px-4 md:py-2 md:text-lg leading-5"
                >
                    <span className="h-5 w-5 border-[1.5px] border-site-primary-600 flex items-center justify-center p-[2px] rounded-full font-bold">
                    <Plus className="w-4 h-4" />
                    </span>
                    Add New Address
                </button>

            </DialogTrigger>

            }
        </div>
    </div>
  )
}
